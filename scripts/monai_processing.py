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

    # Создаем объект SaveImage с указанием, что сохраняем в PNG через PILWriter
    try:
        pil_image = Image.fromarray(image, mode="RGBA")
        pil_image.save("out/test_image.png")
    except Exception as e:
        print(f"Direct save error: {e}")



    # Формируем путь к обработанному файлу: обратите внимание, что в случае PILWriter формат может быть определен автоматически
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed.png")

    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
