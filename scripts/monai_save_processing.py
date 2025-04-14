#!/usr/bin/env python3
import sys
import json
import os
import torch
from monai.transforms import (
    Compose,
    LoadImage,
    Rotate,
    ScaleIntensity,
    SaveImage,
    ToTensor,
)

def main():
    # Check for required arguments
    if len(sys.argv) < 3:
        print(json.dumps({"error": "Not enough arguments. Provide input_path and output_dir"}))
        return

    input_path = sys.argv[1]
    output_dir = sys.argv[2]

    # Before the `Rotate` transform
    image, meta_data = LoadImage(image_only=False)(input_path)
    print(f"Image shape: {image.shape}, Image dtype: {image.dtype}")

    # Check if the input file exists
    if not os.path.exists(input_path):
        print(json.dumps({"error": f"Input file not found: {input_path}"}))
        return

    # Create the output directory if it does not exist
    os.makedirs(output_dir, exist_ok=True)

    # Define the transformation pipeline
    transforms = Compose([
        LoadImage(image_only=False),                # Load image along with metadata
        Rotate(angle=90, align_corners=True),       # Rotate 90 degrees
        ScaleIntensity(),                           # Optional normalization to [0, 1]
        ToTensor(),                                 # Convert to PyTorch Tensor
    ])

    # Define the saving transform
    save_image = SaveImage(
        output_dir=output_dir,                      # Directory to save
        output_postfix="processed",                # Postfix for the output image
        output_dtype=torch.uint8,                  # Save as uint8 type
        separate_folder=False,                     # Save directly in output_dir
    )

    try:
        # Apply transformations
        image, meta_data = transforms(input_path)

        # Save the resulting image with metadata
        saved_path = save_image(image, meta_data)

        # Output success message with saved image path
        result = {"message": "Image successfully processed and saved.", "processed": saved_path}
        print(json.dumps(result))

    except Exception as e:
        print(json.dumps({"error": f"Error during image processing: {str(e)}"}))


if __name__ == '__main__':
    main()
