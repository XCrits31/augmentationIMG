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
        ToTensor()
    ])

    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при обработке изображения: {str(e)}"}))
        return


    if isinstance(image, MetaTensor):
        image = torch.as_tensor(image)  # Convert to PyTorch Tensor
        image = image.numpy()

    try:
            # Ensure the image is in NumPy format
        if isinstance(image, torch.Tensor):
            image = image.numpy()  # Convert tensor to NumPy array
        
            # Automatically detect and adjust axis order
            # Check if the image has 3 dimensions (e.g., RGB or grayscale)
        if image.ndim == 3:
            if image.shape[0] in (1, 3, 4):  # PyTorch format (Channels, Height, Width
                image = np.transpose(image, (1, 2, 0))  # Convert to (Height, Width, Channels)

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
        pil_image = Image.fromarray(image.astype(np.uint8), mode=mode)

        # Save the image to the output path
        pil_image.save("out/saved_image.png")
    except Exception as e:
        print(json.dumps({"error": f"Failed to save the image: {str(e)}"}))
        return



    # Формируем путь к обработанному файлу: обратите внимание, что в случае PILWriter формат может быть определен автоматически
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed.png")

    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
