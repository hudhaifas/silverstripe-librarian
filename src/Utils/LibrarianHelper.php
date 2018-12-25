<?php

use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Member;

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
 * @version 1.0, Sep 6, 2016 - 9:28:31 PM
 */
class LibrarianHelper {

    private static $groups = ['librarians'];
    private static $libraryID = 1310;
    private static $patronID = 2;
    private static $bookID = 3;

    /**
     * Checks if the given member is allowed to access the Library module 
     * 
     * @param type $member
     * @return type
     */
    public static function is_librarian($member = false) {
        // Get current member
        if (!$member) {
            $member = Member::currentUser();
        }

        // Allow edit if member in the Librarians group
//        return $member && $member->inGroups(['librarians'));
        return true;
//        return $member && $member->inGroups($this->config()->groups);
    }

    /**
     * Returns all books volumes listed in a catalog
     * 
     * @param type $catalogID
     * @return \ArrayList
     */
    public static function get_catalog_books($catalogID = false) {
        $all = BookVolume::get();
        $results = new ArrayList();

        if ($catalogID) {
            foreach ($all as $volume) {
                if ($volume->inCatalog($catalogID)) {
                    $results->push($volume);
                }
            }

            return $results;
        } else {
            return $all;
        }
    }

    public static function get_overdue() {
        $volumes = BookVolume::get();
        $results = new ArrayList();

        foreach ($volumes as $volume) {
            if ($volume->isOverDue()) {
                $results->push($volume);
            }
        }

        return $results;
    }

    /// Librarian Functions ///
    public static function lend_book($patron, $book) {
        if (!$patron || !$book) {
            return false;
        }
        if (!$patron->canBorrow()) {
            return false;
        }

        $loan = new BookLoan();

        $now = date('m-d-Y');
        $loan->LoanDate = $now;
        $loan->BookID = $book->ID;
        $loan->PatronID = $patron->ID;
        $loan->write();

        $book->LoanID = $loan->ID;
        $book->write();
    }

    public static function return_book($loan) {
        if (!$loan) {
            return;
        }

        $loan->delete();
//        $book = $loan->Book();
//        $book->LoanID = null;
//        $book->write();
//
//        $patron = $loan->Patron();
//        $patron->Loans()->remove($loan);
//
//        $patron->write();
//        die('Returned');
    }

    /// Search on catalog ///
    public static function search_catalog($request, $searchCriteria = []) {
        $start = ($request->getVar('start')) ? (int) $request->getVar('start') : 0;
        $limit = 20; //$this->limits['catalog'];

        $context = singleton('BookVolume')->getDefaultSearchContext();
        $query = $context->getQuery($searchCriteria, null, ['start' => $start, 'limit' => $limit]);
        $records = $context->getResults($searchCriteria, null, ['start' => $start, 'limit' => $limit]);

        if ($records) {
//            $records = $this->getPaginated($records);
            $records = new PaginatedList($records, $request);
            $records->setPageStart($start);
            $records->setPageLength($limit);
            $records->setTotalItems($query->count());
        }

        return $records;
    }

    public static function search_books($request, $searchCriteria = []) {
        $start = ($request->getVar('start')) ? (int) $request->getVar('start') : 0;
        $limit = 9; //$this->limits['catalog'];

        $context = singleton('Book')->getDefaultSearchContext();
        $query = $context->getQuery($searchCriteria, null, ['start' => $start, 'limit' => $limit]);
        $records = $context->getResults($searchCriteria, null, ['start' => $start, 'limit' => $limit]);

        if ($records) {
//            $records = $this->getPaginated($records);
            $records = new PaginatedList($records, $request);
            $records->setPageStart($start);
            $records->setPageLength($limit);
            $records->setTotalItems($query->count());
        }

        return $records;
    }

    public static function search_all_books($request, $term) {
        $records = [];

        // to fetch books that's name contains the given search term
        $books = DataObject::get('Book')->filterAny([
            'Name:PartialMatch' => $term,
        ]);

        foreach ($books as $o) {
            $records[] = $o;
        }

        // to fetch authors that's name contains the given search term
        $authors = DataObject::get('BookAuthor')->filterAny([
            'NickName:PartialMatch' => $term,
            'FirstName:PartialMatch' => $term,
            'LastName:PartialMatch' => $term,
            'SurName:PartialMatch' => $term,
        ]);

        foreach ($authors as $o) {
            foreach ($o->Books() as $b) {
                $records[] = $b;
            }
        }

        $result = new ArrayList($records);
        $result->removeDuplicates();
        return $result;
    }

    /**
     * Generates a barcode image
     * @return type
     */
    public static function generate_barcode($codabar) {
        $barcode = new Codabar();
        $barcode->setData($codabar);
        $barcode->setDimensions(600, 100);
        $barcode->draw();

        $filename = 'assets/librarian/barcodes/' . $codabar . '.jpg';
        $barcode->save(Director::baseFolder() . '/' . $filename);

        $folder = Folder::find_or_make('/librarian/barcodes/');

        $image = new Image();
        $image->Filename = $filename;
        $image->Title = $codabar;

        // TODO This should be auto-detected
        $image->ParentID = $folder->ID;
        $image->write();

        return $image->ID;
    }

}
