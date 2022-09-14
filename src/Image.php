<?php
/**
 * @link https://cms.skeeks.com/
 * @copyright Copyright (c) 2010 SkeekS
 * @license https://cms.skeeks.com/license/
 * @author Semenov Alexander <semenov@skeeks.com>
 */

namespace skeeks\imagine;

use Imagine\Image\BoxInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 */
class Image extends BaseImage
{
    /**
     * Creates a thumbnail image.
     *
     * If one of thumbnail dimensions is set to `null`, another one is calculated automatically based on aspect ratio of
     * original image. Note that calculated thumbnail dimension may vary depending on the source image in this case.
     *
     * If both dimensions are specified, resulting thumbnail would be exactly the width and height specified. How it's
     * achieved depends on the mode.
     *
     * If `ImageInterface::THUMBNAIL_OUTBOUND` mode is used, which is default, then the thumbnail is scaled so that
     * its smallest side equals the length of the corresponding side in the original image. Any excess outside of
     * the scaled thumbnail’s area will be cropped, and the returned thumbnail will have the exact width and height
     * specified.
     *
     * If thumbnail mode is `ImageInterface::THUMBNAIL_INSET`, the original image is scaled down so it is fully
     * contained within the thumbnail dimensions. The rest is filled with background that could be configured via
     * [[Image::$thumbnailBackgroundColor]] and [[Image::$thumbnailBackgroundAlpha]].
     *
     * @param string|resource|ImageInterface $image either ImageInterface, resource or a string containing file path
     * @param int $width the width in pixels to create the thumbnail
     * @param int $height the height in pixels to create the thumbnail
     * @param string $mode mode of resizing original image to use in case both width and height specified
     * @return ImageInterface
     */
    public static function thumbnailV2($image, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
    {
        $img = self::ensureImageInterfaceInstance($image);

        /** @var BoxInterface $sourceBox */
        $sourceBox = $img->getSize();
        $thumbnailBox = static::getThumbnailBox($sourceBox, $width, $height);

        /*if (self::isUpscaling($sourceBox, $thumbnailBox)) {
            return $img->copy();
        }*/

        $img = $img->thumbnail($thumbnailBox, $mode);

        if ($mode == ManipulatorInterface::THUMBNAIL_OUTBOUND) {
            return $img;
        }

        $size = $img->getSize();

        /*if ($size->getWidth() == $width && $size->getHeight() == $height) {
            return $img;
        }*/

        $palette = new RGB();
        $color = $palette->color(static::$thumbnailBackgroundColor, static::$thumbnailBackgroundAlpha);

        // create empty image to preserve aspect ratio of thumbnail
        $thumb = static::getImagine()->create($thumbnailBox, $color);

        // calculate points
        $startX = 0;
        $startY = 0;
        if ($size->getWidth() < $width) {
            $startX = ceil(($width - $size->getWidth()) / 2);
        }
        if ($size->getHeight() < $height) {
            $startY = ceil(($height - $size->getHeight()) / 2);
        }

        $thumb->paste($img, new Point($startX, $startY));

        return $thumb;
    }
}
