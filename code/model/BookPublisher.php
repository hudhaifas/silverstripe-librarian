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
 * @version 1.0, Aug 27, 2016 - 9:50:21 AM
 */
class BookPublisher
        extends LibraryObject {

    private static $db = array(
        'Name' => 'Varchar(255)',
        'Address' => 'Varchar(255)',
        'Phone' => 'Varchar(20)',
    );
    private static $translate = array(
        'Name',
        'Address'
    );
    private static $has_one = array(
        'Logo' => 'Image'
    );
    private static $has_many = array(
        'BookCopies' => 'BookCopy'
    );
    private static $summary_fields = array(
        'ThumbLogo',
        'Name',
        'Address',
        'Phone',
        'BookCopies.Count',
    );
    private static $field_labels = array(
        'ThumbLogo' => 'Logo',
        'BookCopies.Count' => 'Number Of Copies'
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Name'] = _t('Librarian.NAME', "Name");
        $labels['Title'] = _t('Librarian.NAME', "Name");
        $labels['Address'] = _t('Librarian.ADDRESS', "Address");
        $labels['Phone'] = _t('Librarian.PHONE', 'Phone');
        $labels['Logo'] = _t('Librarian.LOGO', 'Logo');
        $labels['ThumbLogo'] = _t('Librarian.LOGO', 'Logo');
        $labels['BookCopies.Count'] = _t('Librarian.NUMBER_OF_COPIES', 'Number Of Copies');
        $labels['BookCopies'] = _t('Librarian.BOOK_COPIES', 'Book Copies');

        return $labels;
    }

    function Link($action = null) {
        return parent::Link("publisher/$this->ID");
    }

    public function Title() {
        $title = $this->Name;

        if ($this->Address) {
            $title .= ' (' . $this->Address . ')';
        }

        return $title;
    }

    public function ThumbLogo() {
        return $this->Logo()->CMSThumbnail();
    }

}