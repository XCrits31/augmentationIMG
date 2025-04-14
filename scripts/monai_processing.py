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
    ScaleIntensity,
    Resize,
    Rotate,
    Zoom,
    SaveImage,
    ToTensor,
    EnsureChannelFirst

)

def main():
    if len(sys.argv) < 3:
        print(json.dumps({"error": "need more argv"}))
        return

    input_path = sys.argv[1]
    output_dir = sys.argv[2]

    if not os.path.exists(input_path):
        print(json.dumps({"error": f"missing input file {input_path}"}))
        return

    os.makedirs(output_dir, exist_ok=True)

    transforms = Compose([
        LoadImage(image_only=True),
        EnsureChannelFirst(),
        Rotate(angle=90),
        Zoom(zoom=(1.5, 1.5)),
        ToTensor(),
    ])

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
