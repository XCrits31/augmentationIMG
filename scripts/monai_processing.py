import site
import sys
import json
import os
import numpy as np
import torch
from monai.data import MetaTensor
from PIL import Image
from monai.transforms import (
    Compose,
    LoadImage,
    EnsureChannelFirst,
    RandAdjustContrast,
    RandFlip,
    RandRotate,
    Zoom,
    RandGaussianNoise,
    RandScaleIntensity,
    Rand2DElastic,
    ToTensor,
)

def build_composite_transformations(transformations):
    """
    Создает объект MONAI `Compose` на основе переданного массива трансформаций.
    Все параметры передаются исключительно из массива, и ошибки поднимаются при их отсутствии.

    Args:
        transformations (list): Список трансформаций и их параметров (например, JSON).

    Returns:
        Compose: Объект с последовательными динамическими преобразованиями.
    """
    transform_list = [
        LoadImage(image_only=True),
        EnsureChannelFirst(),
    ]

    # Проход по всем переданным трансформациям
    for transformation in transformations:
        name = transformation["transformation"]
        params = transformation.get("parameters", {})

        if name == "contrast":
            # RandAdjustContrast: Требует prob, gamma
            if "prob" in params and "gamma" in params:
                prob = float(params["prob"])
                gamma = float(params["gamma"])
                transform_list.append(RandAdjustContrast(prob=prob, gamma=gamma))
            else:
                raise ValueError(f"Missing 'prob' or 'gamma' parameter for transformation '{name}'")

        elif name == "flip":
            # RandFlip: Требует prob, spatial_axis
            if "prob" in params and "axis" in params:
                prob = float(params["prob"])
                axis = int(params["axis"])
                transform_list.append(RandFlip(prob=prob, spatial_axis=axis))
            else:
                raise ValueError(f"Missing 'prob' or 'axis' parameter for transformation '{name}'")

        elif name == "rotate":
            # RandRotate: Требует range_x, prob, keep_size
            required_keys = ["range", "prob", "keep_size"]
            if all(key in params for key in required_keys):
                range_x = float(params["range"])
                prob = float(params["prob"])
                keep_size = params["keep_size"] in [True, "true", "True", 1]  # Приведение к bool
                transform_list.append(RandRotate(range_x=range_x, prob=prob, keep_size=keep_size))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")

        elif name == "zoom":
            # Zoom: Требует zoom (tuple of floats)
            if "zoom" in params:
                zoom_factors = tuple(map(float, params["zoom"]))
                transform_list.append(Zoom(zoom=zoom_factors))
            else:
                raise ValueError(f"Missing 'zoom' parameter for transformation '{name}'")

        elif name == "noise":
            # RandGaussianNoise: Требует mean, std, prob
            required_keys = ["mean", "std", "prob"]
            if all(key in params for key in required_keys):
                mean = float(params["mean"])
                std = float(params["std"])
                prob = float(params["prob"])
                transform_list.append(RandGaussianNoise(mean=mean, std=std, prob=prob))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")

        elif name == "scale_intensity":
            # RandScaleIntensity: Требует factors (tuple of floats), prob
            if "min" in params and "max" in params and "prob" in params:
                factors = (float(params["min"]), float(params["max"]))
                prob = float(params["prob"])
                transform_list.append(RandScaleIntensity(factors=factors, prob=prob))
            else:
                raise ValueError(f"Missing 'min', 'max' or 'prob' parameters for transformation '{name}'")

        elif name == "elastic":
            # Rand2DElastic: Требует magnitude_range, spacing (tuple), prob
            required_keys = ["min_el", "max_el", "space1", "space2", "prob"]
            if all(key in params for key in required_keys):
                magnitude_range = (float(params["min_el"]), float(params["max_el"]))
                spacing = (float(params["space1"]), float(params["space2"]))
                prob = float(params["prob"])
                transform_list.append(Rand2DElastic(magnitude_range=magnitude_range, spacing=spacing, prob=prob))
            else:
                raise ValueError(f"Missing one of {required_keys} for transformation '{name}'")

        else:
            raise ValueError(f"Unsupported transformation '{name}'")

    # Финальный шаг преобразования в тензор
    transform_list.append(ToTensor())

    return Compose(transform_list)

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
    transforms = build_composite_transformations(transformations)
    
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed.png")

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

    print(f"Shape before moveaxis: {image.shape}")

    if len(image.shape) == 3:
        if image.shape[0] == 1:
            image = image[0]
        elif image.shape[0] in [3, 4]:
            image = np.moveaxis(image, 0, -1)
            #image = np.flip(image, axis=2)
    print(f"Shape before moveaxis: {image.shape}")


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


    result = {"message": "image processed", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
