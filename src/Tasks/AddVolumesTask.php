<?php

use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;

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
class AddVolumesTask
        extends BuildTask {

    protected $title = 'Add Volume';
    protected $description = "
            Add Volumes objects to the given book copy.
            
            Parameters:
            - copy: Book Copy ID
            - volumes: Number of volumes
            ";
    protected $enabled = true;

    public function run($request) {
        echo 'Task started..<br />';

        $copyID = $request->getVar('copy');
        $number = $request->getVar('volumes');
        $copy = DataObject::get_by_id(BookCopy::class, $copyID);

        $start = $copy->BookVolumes()->Count();
        echo 'Copy (' . $copy->getTitle() . ') has ' . $start . ' of ' . $number . ' volumes.<br />';
        echo 'Adding new ' . ($number - $start) . ' of ' . $number . ' volumes...<br />';

        for ($i = $start; $i < $number; $i++) {
            $volume = new BookVolume();
            $volume->TheIndex = ($i + 1);
            $volume->BookCopyID = $copy->ID;
            $volume->write();
        }

        echo 'Task finished.';
    }

}
