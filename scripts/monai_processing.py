#!/usr/bin/env python3
import sys
import json
import os

from monai.transforms import (
    Compose,
    LoadImage,
    AddChannel,
    ScaleIntensity,
    Resize,
    SaveImage
)
import nibabel as nib  # MONAI может работать с различными форматами, если требуется

def main():
    # Проверка аргументов: ожидается 2 аргумента: путь к входному изображению и путь к выходной папке
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Недостаточно аргументов. Требуется input_path и output_dir"}))
        return

    input_path = sys.argv[1]
    output_dir = sys.argv[2]

    # Проверка существования входного файла
    if not os.path.exists(input_path):
        print(json.dumps({"error": f"Входной файл не найден: {input_path}"}))
        return

    # Убедимся, что выходная директория существует
    os.makedirs(output_dir, exist_ok=True)

    # Генерируем имя выходного файла на основе входного
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed{ext}")

    # Составляем пайплайн MONAI:
    transforms = Compose([
        LoadImage(image_only=True),   # Загружаем изображение (в формате numpy array)
        AddChannel(),                 # Добавляем канал, если требуется
        ScaleIntensity(),             # Масштабируем интенсивность пикселей
        Resize((256, 256))            # Изменяем размер изображения на 256x256 (можно адаптировать)
    ])

    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при обработке изображения: {str(e)}"}))
        return

    # Сохраняем обработанное изображение
    saver = SaveImage(output_dir=output_dir, output_postfix="", separate_folder=False)
    try:
        saver(image, img_name=base_name.replace(ext, "_processed" + ext))
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при сохранении: {str(e)}"}))
        return

    # Возвращаем путь к обработанному изображению
    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
