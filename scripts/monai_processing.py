#!/usr/bin/env python3
import sys
import json
import os
import numpy as np
from monai.transforms import (
    Compose,
    LoadImage,
    ScaleIntensity,
    Resize,
    SaveImage
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
        Resize((256, 256))
    ])

    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при обработке изображения: {str(e)}"}))
        return

    # Если изображение имеет форму (1, H, W) и H или W равны 1, можно попробовать удалить первую размерность, оставив двумерный массив.
    if image.ndim == 3 and image.shape[0] == 1:
        image = image[0]  # Теперь форма должна быть (H, W)

    # Создаем объект SaveImage с указанием, что сохраняем в PNG через PILWriter
    saver = SaveImage(
        output_dir=output_dir,
        output_postfix="_processed",
        output_ext=".png",        # Желательный формат PNG
        writer="PILWriter",       # Используем PILWriter для сохранения в PNG
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
