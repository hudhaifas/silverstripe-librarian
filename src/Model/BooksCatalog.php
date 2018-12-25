<?php

use SilverStripe\Control\Director;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;

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
 * @version 1.0, Aug 27, 2016 - 10:32:49 AM
 */
class BooksCatalog
        extends DataObject {

    private static $db = [
        'Title' => 'Varchar(255)',
        'IsPublic' => 'Boolean'
    ];
    private static $has_one = [
    ];
    private static $has_many = [
    ];
    private static $many_many = [
        'BookVolumes' => BookVolume::class
    ];
    private static $belongs_many_many = [
    ];
    private static $defaults = [
        'Title' => 'Catalog',
        'IsPublic' => '1'
    ];
    private static $searchable_fields = [
        'Title',
        'IsPublic'
    ];
    private static $summary_fields = [
        'Title',
        'IsPublic',
        'BookVolumes.Count',
    ];
    private static $field_labels = [
        'BookVolumes.Count' => 'Number Of Volumes'
    ];

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Title'] = _t('Librarian.TITLE', 'Title');
        $labels['IsPublic'] = _t('Librarian.IS_PUBLIC', 'IsPublic');
        $labels['BookVolumes.Count'] = _t('Librarian.NUMBER_OF_BOOKS', 'Number Of Books');
        $labels['BookVolumes'] = _t('Librarian.BOOK_VOLUMES', 'Book Volumes');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;
        $this->beforeUpdateCMSFields(function ($fields) use ($self) {

//            $field = ListboxField::create(
//                            'BookVolumes', //
//                            'BookVolumes', //
//                            BookVolume::get()->map()->toArray()
//                    )->setMultiple(true);

            $field = new CheckboxSetField(
                    'BookVolumes', //
                    'BookVolumes', //
                    BookVolume::get()->map()->toArray() //
            );


            $fields->removeFieldFromTab('Root', 'BookVolumes');
            $fields->addFieldToTab('Root.Main', $field);
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    public function canView($member = null) {
        return true;
    }

    function Link($action = null) {
        $page = BookVolumesPage::get()->first();

        return $page ? $page->Link("catalog/$this->ID") : null;
    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return Director::absoluteURL($this->Link());
    }

    public function getVolumes($includeReferences = true) {
        $volumes = [];

        foreach ($this->BookVolumes() as $volume) {
            if ($includeReferences || !$volume->IsReference()) {
                $volumes[] = $volume;
            }
        }

        return new ArrayList($volumes);
    }

    public function getRandomVolumes($includeReferences = true) {
        $volumes = [];

        foreach ($this->BookVolumes()->sort('RAND()') as $volume) {
            if ($includeReferences || !$volume->IsReference()) {
                $volumes[] = $volume;
            }
        }

        return new ArrayList($volumes);
    }

    public function getPaginatedVolumes($request, $includeReferences = true, $length = 9) {
        $paginate = new PaginatedList($this->getVolumes($includeReferences), $request);
        $paginate->setPageLength($length);

        return $paginate;
    }

}
