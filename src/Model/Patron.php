<?php

use Gheggie\Barcode\Utils\CodabarNumber;
use HudhaifaS\DOM\Model\ManageableDataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\RequiredFields;
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
 * This class represents the person who borrows and uses the services and books of a library. 
 * 
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Aug 27, 2016 - 9:25:06 AM
 */
class Patron
        extends DataObject
        implements ManageableDataObject {

    private static $table_name = "Patron";
    private static $db = [
        'SerialNumber' => 'Varchar(20)', // Unique codabar number
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'MaxLoans' => 'Int', // Max number of book loans
    ];
    private static $has_one = [
        'Barcode' => Image::class, // Codabar barcode
    ];
    private static $has_many = [
        'Loans' => BookLoan::class
    ];
    private static $defaults = [
        'MaxLoans' => 3
    ];
    private static $summary_fields = [
        'FirstName',
        'LastName',
        'Email',
        'Phone',
        'MaxLoans',
        'Loans.Count',
    ];
    private static $patronDigit = 2;

    protected function onBeforeWrite() {
        parent::onBeforeWrite();

        // The patron serialnumber is auto-generated form his id, library id and the checksum 
        if (!$this->SerialNumber) {
            $sn = $this->ID;
            $codabar = new CodabarNumber($sn, $this->config()->patronDigit);
            $this->SerialNumber = $codabar->getCodabar();
        }

        // The patron barcode is auto-generated from his serialnumber
        if (!$this->BarcodeID) {
            $this->BarcodeID = LibrarianHelper::generate_barcode($this->SerialNumber);
        }
    }

    public function fieldLabels($includerelations = true) {
        $labels = parent::fieldLabels($includerelations);

        $labels['Loans'] = _t('Librarian.LOANS', "Loans");
        $labels['FirstName'] = _t('Librarian.FIRSTNAME', "FirstName");
        $labels['LastName'] = _t('Librarian.LASTNAME', 'LastName');
        $labels['Email'] = _t('Librarian.EMAIL', 'Email');
        $labels['Phone'] = _t('Librarian.PHONE', 'Phone');
        $labels['MaxLoans'] = _t('Librarian.MAX_LOANS', 'Max Loans Number');
        $labels['Loans.Count'] = _t('Librarian.LOANS_COUNT', 'Loans Count');

        return $labels;
    }

    public function getCMSValidator() {
        return new RequiredFields('FirstName', 'LastName', 'MaxLoans');
    }

    function Link($action = null) {
        $page = PatronsPage::get()->first();

        return $page ? $page->Link($action) : null;
    }

    //////// ManageableDataObject ////////
    public function getObjectDefaultImage() {
        return ModuleLoader::getModule('hudhaifas/silverstripe-librarian')->getResource('res/images/default-author.png')->getURL();
    }

    public function getObjectEditLink() {
        return $this->Link("edit/$this->ID");
    }

    public function getObjectEditableImageName() {
        
    }

    public function getObjectImage() {
//        return $this->Photo();
    }

    public function getObjectItem() {
        return $this->renderWith('Library_Item');
    }

    public function getObjectLink() {
        return $this->Link("show/$this->ID");
    }

    public function getObjectNav() {
        
    }

    public function getObjectRelated() {
        
    }

    public function getObjectSummary() {
        return $this->renderWith('Patron_Summary');
    }

    public function getObjectTabs() {
        $lists = [];

        if ($this->Biography) {
            $lists[] = [
                'Title' => _t("Librarian.AUTHOR_OVERVIEW", "Author Overview"),
                'Content' => $this->Biography
            ];
        }

        $loans = $this->Loans();
        if ($loans->Count()) {
            $lists[] = [
                'Title' => _t("Librarian.LOANS", "Loans") . " ({$loans->Count()})",
                'Content' => $this
                        ->customise([
                            'Results' => $loans
                        ])
                        ->renderWith('List_Grid')
            ];
        }

        $this->extend('extraTabs', $lists);

        return new ArrayList($lists);
    }

    public function getObjectTitle() {
        return $this->getTitle();
    }

    public function canPublicView() {
        return $this->canView();
    }

    public function getTitle() {
        return $this->FirstName . ' ' . $this->LastName;
    }

    public function getDetails() {
        return $this->FirstName . ' ' . $this->LastName . ' (' . $this->Loans()->Count() . ')';
    }

    /**
     * Checks whether the patron has available books to borrow.
     * @return true if he can boroow, otherwise false.
     */
    public function canBorrow() {
        return $this->Loans()->Count() < $this->MaxLoans;
    }

}
