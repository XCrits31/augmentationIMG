import site
import sys
import json
import os
import numpy as np
import torch
import uuid
import random
from monai.data import MetaTensor
from PIL import Image
from monai.transforms import (
    Compose,
    LoadImage,
    EnsureChannelFirst,
    RandAdjustContrast,
    RandFlip,
    RandRotate,
    RandZoom,
    RandGaussianNoise,
    RandScaleIntensity,
    Rand2DElastic,
    ToTensor,
)

def build_composite_transformations(transformations):
    transform_list = [
        LoadImage(image_only=True),
        EnsureChannelFirst(),
    ]

    for transformation in transformations:
        name = transformation["transformation"]
        params = transformation.get("parameters", {})

        if name == "contrast":
            required_keys = ["gamma_min", "gamma_max", "prob"]
            if all(key in params for key in required_keys):
                gamma = random.uniform(float(params['gamma_min']), float(params['gamma_max']))
                prob = float(params["prob"])
                transform_list.append(RandAdjustContrast(prob=prob, gamma=gamma))
            else:
                raise ValueError(f"Missing 'prob' or 'gamma' parameter for transformation '{name}'")

        elif name == "flip":
            required_keys = ["axis", "prob"]
            if all(key in params for key in required_keys):
                axis = int(params["axis"])
                prob = float(params["prob"])
                transform_list.append(RandFlip(prob=prob, spatial_axis=axis))
            else:
                raise ValueError(f"Missing 'prob' or 'axis' parameter for transformation '{name}'")

        elif name == "rotate":
            required_keys = ["range", "keep_size", "prob"]
            if all(key in params for key in required_keys):
                range_x = float(params["range"])
                prob = float(params["prob"])
                keep_size = params["keep_size"] in [True, "true", "True", 1]  # Приведение к bool
                transform_list.append(RandRotate(range_x=range_x, prob=prob, keep_size=1, mode="bicubic"))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")

        elif name == "zoom":
            required_keys = ["zoom_min", "zoom_max", "prob"]
            if all(key in params for key in required_keys):
                zoom_min = float(params['zoom_min'])
                zoom_max = float(params['zoom_max'])
                prob = float(params["prob"])
                transform_list.append(RandZoom(min_zoom=zoom_min, max_zoom=zoom_max, prob=prob, padding_mode="empty", mode="nearest-exact"))
            else:
                raise ValueError(f"Missing 'zoom' parameter for transformation '{name}'")

        elif name == "noise":
            required_keys = ["mean_min", "mean_max", "std_min", "std_max", "prob"]
            if all(key in params for key in required_keys):
                mean = random.uniform(float(params['mean_min']), float(params['mean_max']))
                std = random.uniform(float(params['std_min']), float(params['std_max']))
                prob = float(params["prob"])
                transform_list.append(RandGaussianNoise(mean=mean, std=std, prob=prob))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")

        elif name == "scale_intensity":
            required_keys = ["min", "max", "prob"]
            if all(key in params for key in required_keys):
                factors = (float(params["min"]), float(params["max"]))
                prob = float(params["prob"])
                transform_list.append(RandScaleIntensity(factors=factors, prob=prob))
            else:
                raise ValueError(f"Missing 'min', 'max' or 'prob' parameters for transformation '{name}'")

        elif name == "elastic":
            required_keys = ["min_el", "max_el", "space1", "space2", "prob"]
            if all(key in params for key in required_keys):
                magnitude_range = (float(params["min_el"]), float(params["max_el"]))
                spacing = (int(params["space1"]), int(params["space2"]))
                prob = float(params["prob"])
                transform_list.append(Rand2DElastic(magnitude_range=magnitude_range, spacing=spacing, prob=prob))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")
    transform_list.append(ToTensor())

    return transform_list

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"error": "need more argv"}))
        return

    input_path = sys.argv[1]
    output_dir = sys.argv[2]
    transformations_json = sys.argv[3]

    transformations = json.loads(transformations_json)

    if not os.path.exists(input_path):
        print(json.dumps({"error": f"missing input file {input_path}"}))
        return

    os.makedirs(output_dir, exist_ok=True)

    #transforms = Compose([
        #LoadImage(image_only=True),
        #EnsureChannelFirst(),
        #Rotate(angle=90),
        #Zoom(zoom=(1.5, 1.5)),
        #ToTensor(),
    #])
    transforms = Compose(build_composite_transformations(transformations))
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    unique_id = uuid.uuid4().hex[:8]
    output_path = os.path.join(output_dir, f"{name}_processed_{unique_id}.png")

    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"error transforms: {str(e)}"}))
        return

    tensor_output_path = os.path.join(output_dir, f"{name}_processed_{unique_id}.pt")
    try:
        torch.save(image, tensor_output_path)
    except Exception as e:
        print(json.dumps({"error": f"Failed to save tensor: {str(e)}"}))
        return

    if isinstance(image, MetaTensor):
        image = torch.as_tensor(image)
        image = image.numpy()


    if torch.is_tensor(image):
        image = image.numpy()

    if len(image.shape) == 3:
        if image.shape[0] == 1:
            image = image[0]
        elif image.shape[0] in [3, 4]:
            image = np.moveaxis(image, 0, -1)
            #image = np.flip(image, axis=2)


    # Normalize to uint8
    #if image.dtype != np.uint8:
        #image = (np.clip(image, 0, 1) * 255).astype(np.uint8)

    if image.ndim == 2:
        mode = "L"
    elif image.ndim == 3 and image.shape[-1] == 3:
        mode = "RGB"
    elif image.ndim == 3 and image.shape[-1] == 4:
        mode = "RGBA"
    else:
        raise ValueError(f"Unsupported image shape: {image.shape}")

    try:
        pil_image = Image.fromarray(image.astype(np.uint8), mode=mode)
        pil_image = pil_image.transpose(Image.ROTATE_270)
        pil_image = pil_image.transpose(Image.FLIP_LEFT_RIGHT)
        pil_image.save(output_path)

    except Exception as e:
        print(json.dumps({"error": f"Failed to save the image: {str(e)}"}))
        return

    result = {
        "message": "Image processed successfully!",
        "processed": output_path,
    }

    print(json.dumps(result))



if __name__ == '__main__':
    main()
