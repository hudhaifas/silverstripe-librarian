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
 * @version 1.0, Aug 27, 2016 - 9:24:57 AM
 */
class BookAuthor
        extends LibraryObject {

    private static $db = array(
        'Prefix' => 'Varchar(255)',
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Postfix' => 'Varchar(255)',
        'NickName' => 'Varchar(255)',
        'SurName' => 'Varchar(255)',
        'Biography' => 'Text',
        'BirthYear' => 'Int',
        'DeathYear' => 'Int',
    );
    private static $has_one = array(
        'Photo' => 'Image'
    );
    private static $has_many = array(
    );
    private static $belongs_many_many = array(
        'Books' => 'Book'
    );
    private static $searchable_fields = array(
        'NickName',
        'FirstName',
        'LastName',
        'SurName',
    );
    private static $summary_fields = array(
        'ThumbPhoto',
        'NickName',
        'FirstName',
        'LastName',
        'SurName',
        'Books.Count'
    );
    private static $field_labels = array(
        'ThumbPhoto' => 'Photo',
        'Books.Count' => 'Number Of Books',
    );

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Prefix'] = _t('Librarian.PREFIX', "Prefix");
        $labels['FirstName'] = _t('Librarian.FIRSTNAME', "FirstName");
        $labels['LastName'] = _t('Librarian.LASTNAME', 'LastName');
        $labels['Postfix'] = _t('Librarian.POSTFIX', 'Postfix');
        $labels['NickName'] = _t('Librarian.NICKNAME', 'NickName');
        $labels['Biography'] = _t('Librarian.BIOGRAPHY', "Biography");
        $labels['BirthYear'] = _t('Librarian.BIRTH_YEAR', "BirthYear");
        $labels['DeathYear'] = _t('Librarian.DEATH_YEAR', "DeathYear");
        $labels['Photo'] = _t('Librarian.PHOTO', "Photo");
        $labels['SurName'] = _t('Librarian.SURNAME', "SurName");
        $labels['ThumbPhoto'] = _t('Librarian.PHOTO', 'Photo');
        $labels['Books.Count'] = _t('Librarian.NUMBER_OF_BOOKS', 'Number Of Books');
        $labels['Books'] = _t('Librarian.BOOKS', 'Books');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $this->beforeUpdateCMSFields(function ($fields) use ($self) {
            $this->reorderField($fields, 'Prefix', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'NickName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'FirstName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'LastName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'SurName', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Postfix', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'Photo', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'BirthYear', 'Root.Main', 'Root.Main');
            $this->reorderField($fields, 'DeathYear', 'Root.Main', 'Root.Main');

            if ($field = $fields->fieldByName('Root.Main.Biography')) {
                $fields->removeFieldFromTab('Root.Main', 'Biography');
//                $fields->addFieldToTab('Root.Main', $field);
                $holder = ToggleCompositeField::create(
                                'CustomOverview', //
                                _t('Librarian.OVERVIEW', 'Overview'), //
                                array(
                            $field,
                                )
                );
                $holder->setHeadingLevel(4);
                $holder->addExtraClass('custom-summary');
                $fields->addFieldToTab('Root.Main', $holder);
            }
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();
        $trim = array(
            'Prefix',
            'FirstName',
            'LastName',
            'Postfix',
            'NickName',
            'SurName'
        );

        foreach ($trim as $field) {
            $this->trim($field);
        }
    }

    function Link($action = null) {
        return parent::Link("books/author/$this->ID");
    }

    public function getTitle() {
        return $this->ShortName();
    }

    public function ThumbPhoto() {
        return $this->Photo()->CMSThumbnail();
    }

    public function ShortName() {
        $name = '';

        if ($this->SurName) {
            $name .= '(' . $this->SurName . ') ';
        }

        $name .= $this->FirstName . ' ' . $this->LastName;

        return $name;
    }

    public function FullName() {
        $name = '';

        if ($this->SurName) {
            $name .= '(' . $this->SurName . ')';
        }

        if ($this->Prefix) {
            $name .= ' ' . $this->Prefix;
        }

        if ($this->NickName) {
            $name .= ' ' . $this->NickName;
        }

        $name .= ' ' . $this->FirstName . ' ' . $this->LastName;

        if ($this->Postfix) {
            $name .= ' ' . $this->Postfix;
        }

        return $name;
    }

    public function OptionName() {
        $name = $this->FirstName . ' ' . $this->LastName;

        if ($this->SurName) {
            $name .= ' (' . $this->SurName . ')';
        }

        return $name;
    }

    public function booksID() {
        foreach ($this->Books() as $book) {
            
        }
    }

    public function getRandomBooks($num = 5) {
        $books = $this->getComponents('Books', '', 'RAND()', '', $num);
        return $books;
    }

}