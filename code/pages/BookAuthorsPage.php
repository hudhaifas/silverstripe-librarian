<?php

/*
 * MIT License
 *  
 * Copyright (c) 2017 Hudhaifa Shatnawi
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
 * @version 1.0, Dec 9, 2017 - 1:35:22 AM
 */
class BookAuthorsPage
        extends DataObjectPage {

    private static $icon = "librarian/images/books.png";
    private static $url_segment = 'authors';
    private static $menu_title = 'Authors';
    private static $allowed_children = 'none';
    private static $description = 'Adds authors page to your library website.';

    public function canCreate($member = false) {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        return (DataObject::get($this->owner->class)->count() > 0) ? false : true;
    }

}

class BookAuthorsPage_Controller
        extends DataObjectPage_Controller {

    protected function getObjectsList() {
        return DataObject::get('BookAuthor');
    }

    protected function searchObjects($list, $keywords) {
        return $list->filterAny(array(
                    'Prefix:PartialMatch' => $keywords,
                    'FirstName:PartialMatch' => $keywords,
                    'LastName:PartialMatch' => $keywords,
                    'Postfix:PartialMatch' => $keywords,
                    'NickName:PartialMatch' => $keywords,
                    'SurName:PartialMatch' => $keywords,
                    'Biography:PartialMatch' => $keywords,
        ));
    }

    protected function getFiltersList() {
        return null;
    }

}
