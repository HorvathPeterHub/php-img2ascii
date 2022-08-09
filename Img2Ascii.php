<?php

class Img2Ascii
{
    protected string $imageFile;

    protected GdImage $gdImage;

    protected array $imageInfo;

    //protected string $chars = '$@B%8&WM#*oahkbdpqwmZO0QLCJUYXzcvunxrjft/\\|()1{}[]?-_+~i!lI;:,^`';
    protected string $chars = '@#$%?*+;:,.';

    protected int $blockSize = 1;

    protected bool $inverse = false;

    public function __construct(string $imageFile = null)
    {
        if (isset($imageFile))
        {
            $this->setImageFile($imageFile);
        }
    }

    public function setImageFile(string $imageFile): void
    {
        if (!file_exists($imageFile))
        {
            throw new RuntimeException("Image file does not exist: " . $imageFile);
        }

        $this->imageFile = $imageFile;
    }

    public function setBlockSize(int $blockSize): void
    {
        $this->blockSize = $blockSize;
    }

    public function inverse(bool $inverse = true): void
    {
        $this->inverse = $inverse;
    }

    public function setChars(string $chars): void
    {
        $this->chars = $chars;
    }

    public function write(): void
    {
        $this->open();
        $this->walk();
    }

    protected function open(): void
    {
        $info = getimagesize($this->imageFile);
        if (false === $info)
        {
            throw new RuntimeException("Wrong image file: " . $this->imageFile);
        }

        $img = match ($info["mime"])
        {
            "image/jpeg" => \imagecreatefromjpeg($this->imageFile),
            "image/png"  => \imagecreatefrompng($this->imageFile),
            "image/gif"  => \imagecreatefromgif($this->imageFile),
            default      => throw new RuntimeException("Unsupported image type: " . $info["mime"])
        };


        if (false === $img)
        {
            throw new RuntimeException("Wrong image file: " . $this->imageFile);
        }

        $this->imageInfo = $info;
        $this->gdImage = $img;
    }

    protected function walk(): void
    {
        for ($y = 0; $y < $this->imageInfo[1]; $y += $this->blockSize)
        {
            for ($x = 0; $x < $this->imageInfo[0]; $x += $this->blockSize)
            {
                $block = $this->readBlock($x, $y);

                echo $this->blockToAscii($block);
            }
            echo "\n";
        }
    }

    protected function blockToAscii(array $block): string
    {
        $maxBrightness = count($block) * count($block[0]);
        $sumBrightness = 0;
        $len = strlen($this->chars);
        foreach ($block as $row)
        {
            foreach ($row as $pixel)
            {
                // 0..1, from dark to bright
                $brightness = ($pixel["r"] + $pixel["g"] + $pixel["b"]) / (3*255);

                // 0..1, from transparent to opaque
                $alphaRatio = $pixel["a"] / 128;

                $alphaBrightness = (1-$brightness) * $alphaRatio;

                $sumBrightness += $brightness + $alphaBrightness;
            }
        }

        $index = floor($sumBrightness / $maxBrightness * ($len - 1));

        if ($this->inverse)
        {
            $index = -$index;
        }

        return substr($this->chars, $index, 1);
    }

    protected function readBlock(int $x, int $y): array
    {
        $block = [];
        for ($offsetY = 0; $offsetY < $this->blockSize && $y + $offsetY < $this->imageInfo[1]; $offsetY++)
        {
            $row = [];
            for ($offsetX = 0; $offsetX < $this->blockSize && $x + $offsetX < $this->imageInfo[0]; $offsetX++)
            {
                $row[] = $this->rgbAt($x + $offsetX, $y + $offsetY);
            }
            $block[] = $row;
        }

        return $block;
    }

    protected function rgbAt(int $x, int $y): array
    {
        $rgb = imagecolorat($this->gdImage, $x, $y);
        $a = ($rgb >> 24) & 0x7F;
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return [
            "r" => $r,
            "g" => $g,
            "b" => $b,
            "a" => $a,
        ];
    }
}
