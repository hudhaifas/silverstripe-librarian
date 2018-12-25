<?php

use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
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
 * This class represents a copy of the library item (book), and is one or more volumes.
 * 
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Aug 27, 2016 - 9:58:57 AM
 */
class BookCopy
        extends DataObject
        implements ManageableDataObject, SearchableDataObject, SociableDataObject {

    private static $db = [
        'ISBN' => 'Varchar(20)', // 13 digit number ex; 978-3-16-148410-0
        'Title' => 'Varchar(255)',
        'IsReference' => 'Boolean',
        'PublishYear' => 'Int',
        'Edition' => 'Varchar(25)',
        'Collection' => 'Varchar(200)',
        'Shelf' => 'Varchar(200)',
    ];
    private static $has_one = [
        'Cover' => Image::class,
        'Format' => BookFormat::class,
        'Book' => Book::class,
        'Publisher' => BookPublisher::class,
    ];
    private static $has_many = [
        'BookVolumes' => BookVolume::class
    ];
    private static $many_many = [
    ];
    private static $belongs_many_many = [
    ];
    private static $summary_fields = [
        'ThumbCover',
        'Title',
        'IsReference',
        'PublishYear',
        'Edition',
        'Collection',
        'Shelf',
        'BookVolumes.Count'
    ];
    private static $searchable_fields = [
        'ISBN',
        'Title',
    ];
    private static $defaults = [
    ];

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);
        $labels['Book'] = _t('Librarian.BOOK', 'Book');
        $labels['BookTitle'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['Book.ThumbCover'] = _t('Librarian.COVER_IMAGE', 'Cover Image');
        $labels['BookVolumes'] = _t('Librarian.BOOK_VOLUMES', 'Book Volumes');
        $labels['BookVolumes.Count'] = _t('Librarian.NUMBER_OF_VOLUMES', 'Number of Volumes');
        $labels['Collection'] = _t('Librarian.COLLECTION', 'Collection');
        $labels['Cover'] = _t('Librarian.COVER_IMAGE', 'Cover Image');
        $labels['Edition'] = _t('Librarian.EDITION', 'Edition');
        $labels['Format'] = _t('Librarian.FORMAT', 'Format');
        $labels['ISBN'] = _t('Librarian.ISBN', 'ISBN');
        $labels['IsReference'] = _t('Librarian.IS_REFERENCE', 'Is Reference');
        $labels['Name'] = _t('Librarian.BOOK_TITLE', 'Title');
        $labels['Publisher'] = _t('Librarian.PUBLISHER', 'Publisher');
        $labels['PublishYear'] = _t('Librarian.PUBLISH_YEAR', 'Publish Year');
        $labels['Shelf'] = _t('Librarian.SHELF', 'Shelf Location');
        $labels['Title'] = _t('Librarian.BOOK_TITLE', 'Title');

        return $labels;
    }

    public function getCMSFields() {
        $self = & $this;

        $self->beforeUpdateCMSFields(function ($fields) use ($self) {

            if ($field = $fields->fieldByName('Root.Main.Cover')) {
                $field->getValidator()->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);
                $field->setFolderName("librarian");

                $fields->removeFieldFromTab('Root.Main', 'Cover');
                $fields->addFieldToTab('Root.Main', $field);
            }

            $self->reorderField($fields, 'BookID', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'ISBN', 'Root.Main', 'Root.Main');
            $self->reorderField($fields, 'Title', 'Root.Main', 'Root.Main');

            if ($volumesField = $fields->fieldByName('Root.BookVolumes.BookVolumes')) {
                $fields->removeFieldFromTab('Root.BookVolumes', 'BookVolumes');
                $fields->removeFieldFromTab('Root', 'BookVolumes');
                $fields->addFieldToTab('Root.Main', $volumesField);
            }

            $self->reorderField($fields, 'PublishYear', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'Edition', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'Collection', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'Shelf', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'IsReference', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'PublisherID', 'Root.Main', 'Root.Details');
            $self->reorderField($fields, 'FormatID', 'Root.Main', 'Root.Details');
        });

        $fields = parent::getCMSFields();

        return $fields;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();

//        if (!$this->Title) {
//            $this->Title = $this->Book()->Name;
//        }

        if (!$this->BookVolumes() || !$this->BookVolumes()->Count()) {
            $volume = new BookVolume();
            $volume->TheIndex = 1;
            $volume->BookCopy = $this;
            $volume->write();

            $this->BookVolumes()->add($volume);
        }

        // TODO: generate default Index number
    }

    protected function onBeforeDelete() {
        parent::onBeforeDelete();
//        if ($this->BookVolumes() && $this->BookVolumes()->Count()) {
//            foreach ($this->BookVolumes() as $volume) {
//                $volume->delete();
//            }
//        }
    }

    public function canView($member = null) {
        return true;
    }

//    public function getTitle() {
//        return $this->Title ? $this->Title : $this->Book()->Title;
//    }

    /**
     * Show this DataObejct in the sitemap.xml
     */
    function AbsoluteLink($action = null) {
        return $this->Book()->AbsoluteLink($action);
    }

    //////// ManageableDataObject //////// 
    public function getObjectDefaultImage() {
        return LIBRARIAN_DIR . "/images/book-cover.jpg";
    }

    public function getObjectEditLink() {
        
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
        return $this->getCoverImage();
    }

    public function getObjectItem() {
        return $this->renderWith('Library_Item');
    }

    public function getObjectLink() {
        return $this->Book()->getObjectLink();
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        
    }

    public function getObjectSummary() {
        
    }

    public function getObjectTabs() {
        
    }

    public function getObjectTitle() {
        return $this->Title;
    }

    public function canPublicView() {
        return $this->canView();
    }

    //////// SearchableDataObject //////// 
    public function getObjectRichSnippets() {
        $schema = [];

        $schema['@context'] = "http://schema.org";
        $schema['@type'] = "Book";
        if ($this->ISBN) {
            $schema['isbn'] = $this->ISBN;
        }

        if ($this->Edition) {
            $schema['bookEdition'] = $this->Edition;
        }

        if ($this->PublishYear) {
            $schema['datePublished'] = $this->PublishYear;
        }

        if ($this->Publisher()->exists()) {
            $schema['publisher'] = [];
            $schema['publisher']['@type'] = "Organization";
            $schema['publisher']['name'] = $this->Publisher()->getTitle();
            if ($this->Publisher()->Logo()->exists()) {
                $schema['publisher']['logo'] = $this->Publisher()->Logo()->URL;
            }

            if ($this->Publisher()->Address) {
                $schema['publisher']['address']['@type'] = "PostalAddress";
                $schema['publisher']['address']['streetAddress'] = $this->Publisher()->Address;
            }

            $schema['publisher']['telephone'] = $this->Publisher()->Phone;
        }

        switch ($this->Format()->Title) {
            case 'Hardcover':
                $schema['bookFormat'] = "http://schema.org/Hardcover";
                break;

            case 'Paperback':
                $schema['bookFormat'] = "http://schema.org/Hardcover";
                break;

            default:
                break;
        }

        return $schema;
    }

    //////// SociableDataObject //////// 
    public function getSocialDescription() {
        if ($this->Summary) {
            return $this->Summary;
        } elseif ($this->Content) {
            return strip_tags($this->Content);
        } elseif ($this->Explanations) {
            return strip_tags($this->Explanations);
        }

        return $this->getObjectTitle();
    }

    public function getRandomVolumes($num = 2) {
        $volumes = [];
        foreach ($this->BookVolumes()->sort('RAND()') as $volume) {
            $volumes[] = $volume;
        }

        return (new ArrayList($volumes))->limit($num);
    }

    public function getNumberOfVolumes() {
        return $this->BookVolumes()->Count();
    }

    public function getNumberOfPages() {
        $pages = 0;

        foreach ($this->BookVolumes() as $volume) {
            $pages += $volume->NumberOfPages;
        }

        return $pages;
    }

    public function getAvailable() {
        $available = 0;
        return $available;
    }

    /// Book ///
    public function getBookName() {
        return $this->Book()->Name;
    }

    public function getSubject() {
        return $this->Book()->Subject;
    }

    public function getOverview() {
        return $this->Book()->Overview;
    }

    public function getAuthoringYear() {
        return $this->Book()->AuthoringYear;
    }

    public function getLanguage() {
        return $this->Book()->Language;
    }

    public function getCoverImage() {
        $cover = $this->Cover();
        if ($cover && $cover->exists()) {
            return $cover;
        } else if ($book = $this->Book()) {
            return $book->Cover();
        }

        return null;
    }

    public function ThumbCover() {
        return ($cover = $this->getCoverImage()) ? $cover->CMSThumbnail() : null;
    }

    public function getAuthors() {
        return $this->Book()->Authors();
    }

    public function getAuthor() {
        return $this->Book()->getAuthor();
    }

    function reorderField($fields, $name, $fromTab, $toTab, $disabled = false) {
        $field = $fields->fieldByName($fromTab . '.' . $name);

        if ($field) {
            $fields->removeFieldFromTab($fromTab, $name);
            $fields->addFieldToTab($toTab, $field);

            if ($disabled) {
                $field = $field->performDisabledTransformation();
            }
        }

        return $field;
    }

    function removeField($fields, $name, $fromTab) {
        $field = $fields->fieldByName($fromTab . '.' . $name);

        if ($field) {
            $fields->removeFieldFromTab($fromTab, $name);
        }

        return $field;
    }

    function trim($field) {
        if ($this->$field) {
            $this->$field = trim($this->$field);
        }
    }

}
