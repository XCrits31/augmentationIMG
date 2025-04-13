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
    ToTensor
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

    try:
        # Ensure the NumPy array has a valid shape for a PNG image
        if image.ndim == 2:  # Grayscale
            mode = "L"
        elif image.ndim == 3 and image.shape[-1] == 3:  # RGB
            mode = "RGB"
        elif image.ndim == 3 and image.shape[-1] == 4:  # RGBA
            mode = "RGBA"
        else:
            raise ValueError(f"Unsupported image shape: {image.shape}")

        # Create a PIL Image with the correct mode
        pil_image = Image.fromarray(image, mode = mode)

        # Save the image to the output path
        pil_image.save(output_path)
    except Exception as e:
        print(json.dumps({"error": f"Failed to save the image: {str(e)}"}))
        return



    # Формируем путь к обработанному файлу: обратите внимание, что в случае PILWriter формат может быть определен автоматически


    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
