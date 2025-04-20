import site
import sys
import json
import os
import numpy as np
import torch
import uuid
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
                required_keys = ["prob", "gamma"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    gamma = float(params["gamma"])
                    transform_list.append(RandAdjustContrast(prob=prob, gamma=gamma))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "flip":
                required_keys = ["prob", "axis"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    axis = int(params["axis"])
                    transform_list.append(RandFlip(prob=prob, spatial_axis=axis))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "rotate":
                required_keys = ["prob", "range", "keep_size"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    range_x = float(params["range"])
                    keep_size = params["keep_size"] in [True, "true", "True", 1]  # Convert to boolean
                    transform_list.append(RandRotate(range_x=range_x, prob=prob, keep_size=keep_size))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "zoom":
                required_keys = ["prob", "zoom"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    zoom = float(params["zoom"])
                    transform_list.append(RandZoom(zoom=zoom, prob=prob))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "noise":
                required_keys = ["prob", "mean", "std"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    mean = float(params["mean"])
                    std = float(params["std"])
                    transform_list.append(RandGaussianNoise(mean=mean, std=std, prob=prob))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "scale_intensity":
                required_keys = ["prob", "min", "max"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    factors = (float(params["min"]), float(params["max"]))
                    transform_list.append(RandScaleIntensity(factors=factors, prob=prob))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            elif name == "elastic":
                required_keys = ["prob", "min_el", "max_el", "space1", "space2"]
                if all(key in params for key in required_keys):
                    prob = float(params["prob"])
                    magnitude_range = (float(params["min_el"]), float(params["max_el"]))
                    spacing = (int(params["space1"]), int(params["space2"]))
                    transform_list.append(Rand2DElastic(magnitude_range=magnitude_range, spacing=spacing, prob=prob))
                else:
                    missing_keys = [key for key in required_keys if key not in params]
                    raise ValueError(f"Missing {missing_keys} parameter(s) for transformation '{name}'")

            else:
                raise ValueError(f"Unknown transformation: '{name}'")

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
        "processed": output_path
    }

    print(json.dumps(result))



if __name__ == '__main__':
    main()
