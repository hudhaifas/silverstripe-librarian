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
 * @version 1.0, Aug 27, 2016 - 8:51:28 PM
 */
class Library
        extends AbstractLibrary {

    private static $db = array(
        'ItemsPerPage' => 'Int',
    );
    private static $has_one = array(
    );
    private static $has_many = array(
    );
    private static $defaults = array(
        'URLSegment' => 'library',
        'Title' => 'Library',
        'MenuTitle' => 'Library',
        'ItemsPerPage' => 9,
    );
    private static $icon = "librarian/images/books.png";
    private static $url_segment = 'library';
    private static $menu_title = 'library';
    private static $allowed_children = 'none';
    private static $description = 'Adds a library to your website.';

    public function canCreate($member = false) {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        return (DataObject::get($this->owner->class)->count() > 0) ? false : true;
    }

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab("Root.Main", new NumericField('ItemsPerPage', _t('Librarian.ITEMS_PER_PAGE', "Items Per Page")), 'Content');

        return $fields;
    }

}

/**
 * 
 */
class Library_Controller
        extends AbstractLibrary_Controller {

    private static $allowed_actions = array(
        'catalog',
        // Search Actions
        'SearchCatalog',
        'doSearchCatalog',
    );
    private static $url_handlers = array(
        'catalog/$ID' => 'catalog',
    );

    public function init() {
        parent::init();
    }

    /**
     * Not completed yet
     * 
     * @param type $level
     * @return \ArrayList
     */
    public function getMenu($level = 1) {
        if ($level == 1) {
            return parent::getMenu($level);
        } else {

            $result = array(
                'Authors' => array(
                    'Title' => _t('Librarian.AUTHORS', 'Authors'),
                    'Items' => $this->getAuthorsList()
                ),
                'Books' => array(
                    'Title' => _t('Librarian.BOOKS', 'Books'),
                    'Items' => $this->getLatestBooks()
                ),
                'Trends' => array(
                    'Title' => _t('Librarian.TRENDS', 'Trends'),
                    'Items' => $this->getLatestBooks()
                ),
            );
            return new ArrayList($result);
        }
    }

    /// Search Catalog ///
    public function SearchCatalog() {
        $context = singleton('BookVolume')->getDefaultSearchContext();
        $fields = $context->getSearchFields();
        $form = new Form($this, 'SearchCatalog', $fields, new FieldList(new FormAction('doSearchCatalog')));
        $form->setTemplate('Library_SearchCatalog');
//        $form->setFormMethod('GET');
//        $form->disableSecurityToken();
//        $form->setFormAction($this->Link());

        return $form;
    }

    public function doSearchCatalog($data, $form) {
        $results = LibrarianHelper::search_catalog($this->request, $data);
        return $this
                        ->customise(array(
                            'Results' => $results,
//                            'Title' => ''
                        ))
                        ->renderWith(array('Library_Catalog', 'Page'));
    }

    /// Actions ///
    public function catalog() {
        $id = $this->getRequest()->param('ID');

        $catalog = BooksCatalog::get()->byID($id);

        if ($catalog) {
            $paginate = $this->getPaginated($catalog->getVolumes());

            return $this
                            ->customise(array(
                                'Catalog' => $catalog,
                                'Results' => $paginate,
                                'Title' => _t('Librarian.CATALOG_TITLE', '{value} Catalog', array('value' => $catalog->Title))
                            ))
                            ->renderWith(array('Library_Catalog', 'Page'));
        } else {
            return $this->httpError(404, 'That catalog could not be found!');
        }
    }

}