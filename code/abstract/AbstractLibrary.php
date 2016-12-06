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
 * @version 1.0, Sep 7, 2016 - 2:33:44 PM
 */
class AbstractLibrary
        extends Page {

    private static $db = array(
    );
    private static $has_one = array(
    );
    private static $has_many = array(
    );
    private static $defaults = array(
    );

    /**
     */
    private static $group_code = 'librarians';
    private static $group_title = 'Librarians';
    private static $group_permission = 'CMS_ACCESS_CMSMain';

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab("Root.Main", "Content");

        return $fields;
    }

    public function canCreate($member = false) {
        return false;
    }

    protected function onBeforeWrite() {
        parent::onBeforeWrite();
        $this->getUserGroup();
    }

    /**
     * Returns/Creates the librarians group to assign CMS access.
     *
     * @return Group Librarians group
     */
    protected function getUserGroup() {
        $code = $this->config()->group_code;

        $group = Group::get()->filter('Code', $code)->first();

        if (!$group) {
            $group = new Group();
            $group->Title = $this->config()->group_title;
            $group->Code = $code;

            $group->write();

            $permission = new Permission();
            $permission->Code = $this->config()->group_permission;

            $group->Permissions()->add($permission);
        }

        return $group;
    }

}

class AbstractLibrary_Controller
        extends Page_Controller {

    private static $allowed_actions = array(
        'books',
        'book',
        'bookvolume',
        // Search Actions
        'SearchBook',
        'doSearchBook'
    );
    private static $url_handlers = array(
        'books/$action/$ID' => 'books',
        'book/$ID' => 'book',
        'copy/$ID' => 'bookcopy',
        'volume/$ID' => 'bookvolume',
    );

    public function init() {
        parent::init();

        Requirements::css("librarian/css/librarian.css");
        if ($this->isRTL()) {
            Requirements::css("librarian/css/librarian-rtl.css");
        }
    }

    public function getDBVersion() {
        return DB::get_conn()->getVersion();
    }

    /// Pagination ///
    public function getPaginated($list, $length = 9) {
        $paginate = new PaginatedList($list, $this->request);
        $paginate->setPageLength($length);

        return $paginate;
    }

    /// Search Book ///
    public function SearchBook() {
        $context = singleton('Book')->getDefaultSearchContext();
        $fields = $context->getSearchFields();
        $form = new Form($this, 'SearchBook', $fields, new FieldList(new FormAction('doSearchBook')));
        $form->setTemplate('Library_SearchBook');
//        $form->setFormMethod('GET');
//        $form->disableSecurityToken();
//        $form->setFormAction($this->Link());

        return $form;
    }

    public function doSearchBook($data, $form) {
        $term = $data['Form_SearchBook_SearchTerm'];

        $books = LibrarianHelper::search_all_books($this->request, $term);
        $title = _t('Librarian.SEARCH_RESULTS', 'Search Results') . ': ' . $data['Form_SearchBook_SearchTerm'];

        if ($books) {
            $paginate = $this->getPaginated($books);

            return $this
                            ->customise(array(
                                'Books' => $books,
                                'Results' => $paginate,
                                'Title' => $title
                            ))
                            ->renderWith(array('Library_Books', 'Page'));
        } else {
            return $this->httpError(404, 'No books could be found!');
        }
    }

    /// Sub Pages ///
    public function books() {
        $action = $this->getRequest()->param('action');
        $id = $this->getRequest()->param('ID');
        $author = null;

        if ($action == 'author' && $id) {
            $author = DataObject::get_by_id('BookAuthor', $id);
            $books = $author->Books();
        } else if ($action == 'category' && $id) {
            $books = DataObject::get_by_id('BookCategory', $id)->Books();
        } else {
            $books = Book::get();
        }

        if ($books) {
            $paginate = $this->getPaginated($books);

            return $this
                            ->customise(array(
                                'Books' => $books,
                                'Results' => $paginate,
                                'Author' => $author,
                                'Title' => _t('Librarian.BOOKS_LIST', 'Books List')
                            ))
                            ->renderWith(array('Library_Books', 'Page'));
        } else {
            return $this->httpError(404, 'No books could be found!');
        }
    }

    public function book() {
        $id = $this->getRequest()->param('ID');

        $book = Book::get()->byID($id);

        if ($book) {
            $this->etalage(140, 205);

            return $this
                            ->customise(array(
                                'Book' => $book,
                                'Title' => $book->Title
                            ))
                            ->renderWith(array('Library_Book', 'Page'));
        } else {
            return $this->httpError(404, 'That book could not be found!');
        }
    }

    public function bookcopy() {
        $id = $this->getRequest()->param('ID');

        $copy = BookCopy::get()->byID($id);

        if ($copy) {
            return $this
                            ->customise(array(
                                'Copy' => $copy,
                                'Title' => $copy->Title
                            ))
                            ->renderWith(array('Library_Copy', 'Page'));
        } else {
            return $this->httpError(404, 'That book copy could not be found!');
        }
    }

    public function bookvolume() {
        $id = $this->getRequest()->param('ID');

        $volume = BookVolume::get()->byID($id);

        if ($volume) {
            $this->etalage(280, 410);

            return $this
                            ->customise(array(
                                'Volume' => $volume,
                                'Title' => $volume->getTitle()
                            ))
                            ->renderWith(array('Library_Volume', 'Page'));
        } else {
            return $this->httpError(404, 'That book volume could not be found!');
        }
    }

    /// Get ///
    public function getPublicCatalogs() {
        return BooksCatalog::get()->filter(array('IsPublic' => 1));
    }

    public function getPatronsList() {
        $patrons = Patron::get();
        return $patrons;
    }

    public function getAuthorsList() {
        //TODO: fix this exception on MySQL > 5.7
        if ($this->getDBVersion() > '5.6') {
            $authors = BookAuthor::get();
        } else {
            $authors = BookAuthor::get()
                    ->setQueriedColumns(['ID', 'Title', 'Count(*)'])
                    ->leftJoin('Book_Authors', 'ba.BookAuthorID = BookAuthor.ID', 'ba')
                    ->sort('Count(*) DESC')
                    ->alterDataQuery(function($query) {
                $query->groupBy('BookAuthor.ID');
            });
        }

        return $authors;
    }

    public function getCategoriesList() {
        //TODO: fix this exception on MySQL > 5.7
        if ($this->getDBVersion() > '5.6') {
            $categories = BookCategory::get();
        } else {
            $categories = BookCategory::get()
                    ->setQueriedColumns(['ID', 'Title', 'Count(*)'])
                    ->leftJoin('Book_Categories', 'bc.BookCategoryID = BookCategory.ID', 'bc')
                    ->sort('Count(*) DESC')
                    ->alterDataQuery(function($query) {
                $query->groupBy('BookCategory.ID');
            });
        }

        return $categories;
    }

    public function getPublishersList() {
        return BookPublisher::get()->filter(array());
//        $publishers = BookPublisher::get()
//                ->setQueriedColumns(['ID', 'Title', 'Count(*)'])
//                ->leftJoin('Book_Authors', 'ba.BookPublisherID = BookPublisher.ID', 'ba')
//                ->sort('Count(*) DESC')
//                ->alterDataQuery(function($query) {
//            $query->groupBy('BookPublisher.ID');
//        });
//
//        return $publishers;
    }

    public function getAuthorBooksss() {
        $author = BookAuthor::get()->filter(array('ID' => 3));

        $result = array();
        foreach ($author->Books() as $book) {
//            $result[] = $book;
        }

        return new ArrayList($result);

//        return Book::get()->filter('Authors.ID:partialmatch', 3);
    }

    public function getCategoryBooks($CategoryID) {
        return BookCategory::get_by_id($CategoryID)->Books();
    }

    public function getPublisherBooks($PublisherID) {
        return BookPublisher::get_by_id($PublisherID)->Books();
    }

    public function getBooksList($filters = array()) {
        return DataObject::get('Book')->filter($filters);
    }

    public function getVolumesList($filters = array()) {
        return DataObject::get('BookVolume')->filter($filters);
    }

    public function getLatestBooks() {
        return Book::get()->filter(array())->sort('Created DESC');
    }

    public function getBookCopies($BookID) {
        return Book::get_by_id($BookID)->BookCopies();
    }

    public function getBookAuthors($BookID) {
        return Book::get_by_id($BookID)->Authors();
    }

    public function getBookCategories($BookID) {
        return Book::get_by_id($BookID)->Categories();
    }

    private function etalage($w, $h) {
        $dir = $this->isRTL() ? 'right' : 'left';

        Requirements::customScript(<<<JS
            jQuery(document).ready(function ($) {
                $('#etalage, .etalager').etalage({
                    thumb_image_width: $w,
                    thumb_image_height: $h,
                    source_image_width: 900,
                    source_image_height: 1200,
                    show_hint: true,
                    align: "$dir",
                });
            });
JS
        );
    }

}