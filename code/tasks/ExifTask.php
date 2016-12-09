<?php

/*
 * MIT License
 *  
 * Copyright (c) 2016 Hudhaifa Shatnawi
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Sep 26, 2016 - 8:54:17 AM
 */
class ExifTask
        extends BuildTask {

    protected $title = 'Exif Orientation';
    protected $description = 'Fix images orientation based on the exif meta tag';
    private $quality = 85;

    public function run($request) {
        $subdir = $request->getVar('dir');
        $dir = BASE_PATH . '/assets/' . $subdir;

        $this->fixDir($dir);
    }

    function fixDir($dir, $space = '') {
        $files = scandir($dir);
        echo($space . 'Dir: ' . $dir . '<br/>');

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (!in_array($file, array(".", ".."))) {
                if ($this->isImage($file)) {
                    echo($space . '&emsp;' . $file);
                    $this->fixOrientation($path);
                } else if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $this->fixDir($dir . DIRECTORY_SEPARATOR . $file, '&emsp;');
                }
            }
        }
    }

    function isImage($file) {
        $fileExt = strtolower(File::get_file_extension($file));

        return in_array($fileExt, array('jpeg', 'jpg', 'JPEG', 'JPG'));
    }

    /**
     * Mobile image correction
     */
    function fixOrientation($imagePath) {
        //Read the JPEG image Exif data to get the Orientation value
        $exif = exif_read_data($imagePath);
        $orientation = @$exif['IFD0']['Orientation'];
        if (!$orientation) {
            $orientation = @$exif['Orientation'];
        }

        //Create a new image from file
        $source = @imagecreatefromjpeg($imagePath);
        if (!$source) {
            return;
        }

        echo(', orientation: ' . $orientation . '<br/>');
        if (!$orientation) {
//            echo('Write image file..');
            imagejpeg($source, $imagePath, $this->quality);  //save output to file system at full quality
            return;
        }

        switch ($orientation) {
            case 3:
                $modifiedImage = imagerotate($source, 180, 0);
                break;

            case 6:
                $modifiedImage = imagerotate($source, -90, 0);
                break;

            case 8:
                $modifiedImage = imagerotate($source, 90, 0);
                break;

            default:
                $modifiedImage = imagerotate($source, 0, 0);
                break;
        }

        echo('Write image file..');
        imagejpeg($modifiedImage, $imagePath, $this->quality);  //save output to file system at full quality
    }

}