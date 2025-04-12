#!/usr/bin/env python3
import sys
import json
import os

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
        ScaleIntensity(),
        Resize((256, 256))
    ])

    try:
        image = transforms(input_path)
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при обработке изображения: {str(e)}"}))
        return

    # Создаём объект сохранения с указанием выхода: здесь output_postfix добавит суффикс к имени файла
    saver = SaveImage(output_dir=output_dir, output_postfix="_processed", output_ext=".png", writer="PILWriter", separate_folder=False)
    try:
        saver(image)  # Вызываем без передачи img_name
    except Exception as e:
        print(json.dumps({"error": f"Ошибка при сохранении: {str(e)}"}))
        return

    # Формируем путь к обработанному файлу
    base_name = os.path.basename(input_path)
    name, ext = os.path.splitext(base_name)
    output_path = os.path.join(output_dir, f"{name}_processed{ext}")

    result = {"message": "Изображение обработано", "processed": output_path}
    print(json.dumps(result))

if __name__ == '__main__':
    main()
