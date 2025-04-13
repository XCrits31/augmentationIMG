#!/usr/bin/env python3
import sys
import json
import os
import numpy as np
import torch
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

    print(f"Image shape: {image.shape}, dtype: {image.dtype}")

# Convert to numpy array if it's still a PyTorch tensor
    if isinstance(image, torch.Tensor):
        image = image.numpy()  # Convert torch tensor to numpy array

# Scale the data to 0–255 if needed
    if image.dtype == np.float32 or image.dtype == np.float64:
        image = (image - image.min()) / (image.max() - image.min())  # Normalize to [0, 1]
        image = (image * 255).astype(np.uint8)  # Scale to [0, 255] and convert to uint8

# Debugging print
    print(f"Processed image shape: {image.shape}, dtype: {image.dtype}")

# Ensure shape is compatible (optional, as MONAI supports this shape)
    if image.ndim == 3 and image.shape[2] == 4:  # RGBA image
        pass  # Shape is fine for RGBA
    else:
        raise ValueError(f"Unexpected shape for RGBA image: {image.shape}")

    # Создаем объект SaveImage с указанием, что сохраняем в PNG через PILWriter
    saver = SaveImage(
        output_dir=output_dir,
        output_postfix="_processed",
        output_ext=".png",        # Желательный формат PNG
        writer="PILWriter",
        image_mode="RGBA",
        separate_folder=False,    # Сохраняем файл непосредственно в output_dir
        print_log=True
    )

    try:
        saver(image)  # Вызываем сохранение без дополнительных аргументов
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при сохранении: {str(e)}"}))
        return

    # Формируем путь к обработанному файлу: обратите внимание, что в случае PILWriter формат может быть определен автоматически
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed.png")

    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
