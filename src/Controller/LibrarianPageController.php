<?php

use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\View\Requirements;

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
 * @version 1.0, Sep 6, 2016 - 2:12:20 PM
 */
class LibrarianPageController extends PageController
{
    private static $allowed_actions = [
        'loans',
        'patron',
        'lend',
        'LendForm',
        'doLend',
        'ReturnForm',
        'doReturn',
    ];
    private static $url_handlers = [
        'loans/$action/$patron' => 'loans',
        'patron/$patron/$action' => 'patron',
    ];

    public function init()
    {
        parent::init();

        Requirements::css("hudhaifas/silverstripe-librarian: res/css/librarian.css");
        if ($this->isRTL()) {
            Requirements::css("hudhaifas/silverstripe-librarian: res/css/librarian-rtl.css");
        }
    }

    public function LendForm()
    {
        $patrons = Patron::get()->map();
        $books = BookVolume::get()->map();

        $loanDate = date("Y-m-d");
        $dueDate = $this->calculateDueDate();

        // Create fields
        $fields = new FieldList(
                DropdownField::create('Patrons', _t('Librarian.PATRONS', 'Patrons'))
                        ->setEmptyString(_t('Librarian.CHOOSE_PATRON', 'Choose Patron'))
                        ->setSource($patrons), //
                DropdownField::create('Books', _t('Librarian.BOOKS', 'Books'))
                        ->setEmptyString(_t('Librarian.CHOOSE_BOOK', 'Choose Book'))
                        ->setSource($books), //
                DateField::create('LoanDate', _t('Librarian.LOAN_DATE', 'Loan Date'), $loanDate)
                        ->setAttribute('data-datepicker', true)
                        ->setAttribute('data-date-format', 'DD-MM-YYYY'), //
                DateField::create('DueDate', _t('Librarian.DUE_DATE', 'Due Date'), $dueDate)
                        ->setAttribute('data-datepicker', true)
                        ->setAttribute('data-date-format', 'DD-MM-YYYY')
        );

        // Create action
        $actions = new FieldList(
                new FormAction('doLend', _t('Librarian.LEND', 'Lend'))
        );

        // Create Validators
        $validator = new RequiredFields();

        return new Form($this, 'LendForm', $fields, $actions, $validator);
    }

    private function calculateDueDate()
    {
        // Calculate the due date
        // 14 days; 24 hours; 60 mins; 60secs
        $dueDate = time() + ($this->config()->loan_period * 24 * 60 * 60);
        return date("Y-m-d", $dueDate);
    }

    public function doLend($data, $form)
    {
        $patron = Patron::get_by_id(Patron::class, $data['Patrons']);
        $book = BookVolume::get_by_id(BookVolume::class, $data['Books']);

        LibrarianHelper::lend_book($patron, $book);
        return $this->owner->redirectBack();
    }

    public function ReturnForm($loanID)
    {
        // Create fields
        $fields = FieldList::create(
                        HiddenField::create('LoanID', 'LoanID', $loanID)
        );

        // Create action
        $actions = FieldList::create(
                        FormAction::create('doReturn', _t('Librarian.RETURN', 'Return'))
        );

        // Create Validators
        $validator = new RequiredFields();
        return new Form($this, 'ReturnForm', $fields, $actions, $validator);
    }

    public function doReturn($data, $form)
    {
        $loan = BookLoan::get_by_id(BookLoan::class, $data['LoanID']);

        LibrarianHelper::return_book($loan);
        return $this->owner->redirectBack();
    }

    public function lend()
    {
        return $this
                        ->customise([
                            'Title' => _t('Librarian.LOAN_LEND', 'Lend')
                        ])
                        ->renderWith(['LibrarianPage_Lend', 'Page']);
    }

    public function loans()
    {
        $filtered = $this->filterLoans();

        if ($filtered['Loans']) {
            $paginate = $this->getPaginated($filtered['Loans'], 8);

            return $this
                            ->customise([
                                'Loans' => $filtered['Loans'],
                                'Results' => $paginate,
                                'ReturnAction' => $filtered['Action'],
                                'Title' => $filtered['Title']
                            ])
                            ->renderWith(['LibrarianPage_Loans', 'Page']);
        } else {
            return $this->httpError(404, 'No loans could be found!');
        }
    }

    public function patron()
    {
        $patronID = $this->getRequest()->param('patron');
        $filtered = $this->filterLoans();

        if ($filtered['Loans']) {
            $paginate = $this->getPaginated($filtered['Loans'], 8);

            return $this
                            ->customise([
                                'Loans' => $filtered['Loans'],
                                'Results' => $paginate,
                                'PatronID' => $patronID,
                                'ReturnAction' => $filtered['Action'],
                                'Title' => $filtered['Title']
                            ])
                            ->renderWith(['LibrarianPage_Patron', 'Page']);
        } else {
            return $this->httpError(404, 'No loans could be found!');
        }
    }

    /**
     * @param $action overdue, return or archive
     * @param $id the patron ID
     * @return list of loans
     */
    private function filterLoans()
    {
        $action = $this->getRequest()->param('action');
        $patronID = $this->getRequest()->param('patron');
        $returnAction = '1';
        $filter = $patronID ? ['PatronID' => $patronID] : [];
        $title = 'Loans';

        if ($action == 'archive') {
            $loans = $this->getLoansArhiveList($filter);
            $returnAction = '0';
            $title = _t('Librarian.LOAN_ARCHIVE', 'Loans Archive');
        } elseif ($action == 'overdue') {
            $loans = $this->getOverdueLoansList($filter);
            $title = _t('Librarian.OVERDUE', 'Overdue Loans');
        } else {
            $loans = $this->getLoansList($filter);
            $title = _t('Librarian.LOAN_RETURN', 'Return Loan');
        }

        return [
            'Loans' => $loans,
            'Action' => $returnAction,
            'Title' => $title
        ];
    }

    public function getLoansList($filters = [])
    {
        return BookLoan::get()->filter($filters);
    }

    public function getLoansArhiveList($filters = [])
    {
        return BookLoanArchive::get()->filter($filters);
    }

    public function getOverdueLoansList($filters = [])
    {
        $dueFilter = [
            'DueDate:LessThan' => date('Y-m-d') . ' 23:59:59'
        ];

        return $this->getLoansList(
                        array_merge($dueFilter, $filters)
        );
    }

    /// Pagination ///
    public function getPaginated($list, $length = 9)
    {
        $paginate = new PaginatedList($list, $this->request);
        $paginate->setPageLength($length);

        return $paginate;
    }

    /// Get ///
    public function getPublicCatalogs()
    {
        return BooksCatalog::get()->filter(['IsPublic' => 1]);
    }

    public function getPatronsList()
    {
        $patrons = Patron::get();
        return $patrons;
    }

    public function getAuthorsList()
    {
        //TODO: fix this exception on MySQL > 5.7
        if ($this->getDBVersion() > '5.6') {
            $authors = BookAuthor::get();
        } else {
            $authors = BookAuthor::get()
                    ->setQueriedColumns(['ID', 'Title', 'Count(*)'])
                    ->leftJoin('Book_Authors', 'ba.BookAuthorID = BookAuthor.ID', 'ba')
                    ->sort('Count(*) DESC')
                    ->alterDataQuery(function ($query) {
                        $query->groupBy('BookAuthor.ID');
                    });
        }

        return $authors;
    }

    public function getCategoriesList()
    {
        //TODO: fix this exception on MySQL > 5.7
        if ($this->getDBVersion() > '5.6') {
            $categories = BookCategory::get();
        } else {
            $categories = BookCategory::get()
                    ->setQueriedColumns(['ID', 'Title', 'Count(*)'])
                    ->leftJoin('Book_Categories', 'bc.BookCategoryID = BookCategory.ID', 'bc')
                    ->sort('Count(*) DESC')
                    ->alterDataQuery(function ($query) {
                        $query->groupBy('BookCategory.ID');
                    });
        }

        return $categories;
    }

    public function getPublishersList()
    {
        return BookPublisher::get()->filter([]);
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

    public function getAuthorBooksss()
    {
        $author = BookAuthor::get()->filter(['ID' => 3]);

        $result = [];
        foreach ($author->Books() as $book) {
//            $result[] = $book;
        }

        return new ArrayList($result);

//        return Book::get()->filter('Authors.ID:partialmatch', 3);
    }

    public function getCategoryBooks($CategoryID)
    {
        return BookCategory::get_by_id($CategoryID)->Books();
    }

    public function getPublisherBooks($PublisherID)
    {
        return BookPublisher::get_by_id($PublisherID)->Books();
    }

    public function getBooksList($filters = [])
    {
        return DataObject::get(Book::class)->filter($filters);
    }

    public function getVolumesList($filters = [])
    {
        return DataObject::get(BookVolume::class)->filter($filters);
    }

    public function getLatestBooks()
    {
        return Book::get()->filter([])->sort('Created DESC');
    }

    public function getBookCopies($BookID)
    {
        return Book::get_by_id($BookID)->BookCopies();
    }

    public function getBookAuthors($BookID)
    {
        return Book::get_by_id($BookID)->Authors();
    }

    public function getBookCategories($BookID)
    {
        return Book::get_by_id($BookID)->Categories();
    }
}
