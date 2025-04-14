#!/usr/bin/env python3
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
    ScaleIntensity,
    Resize,
    Rotate,
    SaveImage,
    ToTensor,
    EnsureChannelFirst

)

def main():
    # Проверка аргументов: требуется 2 аргумента - input_path и output_dir
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Недостаточно аргументов. Требуется input_path и output_dir"}))
        return

    input_path = sys.argv[1]
    output_dir = sys.argv[2]

    if not os.path.exists(input_path):
        print(json.dumps({"error": f"Входной файл не найден: {input_path}"}))
        return

    os.makedirs(output_dir, exist_ok=True)

    # Пайплайн преобразования: загрузка, добавление канала, масштабирование интенсивности, изменение размера
    transforms = Compose([
        LoadImage(image_only=True),
        EnsureChannelFirst(),
        Rotate(angle=90),
        ToTensor(),
    ])
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed.png")
    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при обработке изображения: {str(e)}"}))
        return


    if isinstance(image, MetaTensor):
        image = torch.as_tensor(image)  # Convert to PyTorch Tensor
        image = image.numpy()


    if torch.is_tensor(image):  # Convert PyTorch to NumPy if necessary
        image = image.numpy()


    # Normalize to uint8
    if image.dtype != np.uint8:
        image = (np.clip(image, 0, 1) * 255).astype(np.uint8)

    # Check mode for PIL
    if image.ndim == 2:  # Grayscale
        mode = "L"
    elif image.ndim == 3 and image.shape[-1] == 3:  # RGB
        mode = "RGB"
    elif image.ndim == 3 and image.shape[-1] == 4:  # RGBA
        mode = "RGBA"
    else:
        raise ValueError(f"Unsupported image shape: {image.shape}")

    try:
        print(f"-Final image shape: {image.shape}, Data type: {image.dtype}")

    # Save the image as PNG
        pil_image = Image.fromarray(image, mode=mode)
        pil_image.save(output_path)
        print(f"Final image shape: {image.shape}, Mode: {mode}, Data type: {image.dtype}")

    except Exception as e:
        print(json.dumps({"error": f"Failed to save the image: {str(e)}"}))
        return



    # Формируем путь к обработанному файлу: обратите внимание, что в случае PILWriter формат может быть определен автоматически


    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
