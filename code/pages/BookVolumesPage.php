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
class BookVolumesPage
        extends DataObjectPage {

    private static $icon = "librarian/images/books.png";
    private static $url_segment = 'shelf';
    private static $menu_title = 'Shelf';
    private static $allowed_children = 'none';
    private static $description = 'Adds shelf page to your library website.';

    public function canCreate($member = false) {
        if (!$member || !(is_a($member, 'Member')) || is_numeric($member)) {
            $member = Member::currentUserID();
        }

        return (DataObject::get($this->owner->class)->count() > 0) ? false : true;
    }

}

class BookVolumesPage_Controller
        extends DataObjectPage_Controller {

    private static $allowed_actions = array(
        'catalog',
    );
    private static $url_handlers = array(
        'catalog/$ID' => 'catalog',
    );

    protected function getObjectsList() {
        return DataObject::get('BookVolume')
                        ->filterByCallback(function($record) {
                            return $record->canView();
                        });
    }

    protected function searchObjects($list, $keywords) {
        return $list->filterAny(array(
                    'Name:PartialMatch' => $keywords,
                    'Subject:PartialMatch' => $keywords,
                    'Overview:PartialMatch' => $keywords,
                    'OriginalPublish' => $keywords,
                    'Language:PartialMatch' => $keywords,
        ));
    }

    protected function getFiltersList() {
        return null;
    }

    /// Actions ///
    public function catalog() {
        $id = $this->getRequest()->param('ID');

        $catalog = BooksCatalog::get()->byID($id);

        if ($catalog) {
            $paginated = PaginatedList::create($catalog->getVolumes(), $this->request)
                    ->setPageLength($this->PageLength)
                    ->setPaginationGetVar('s');

            $data = array(
                'Results' => $paginated
            );

            return $this->customise($data)
                            ->renderWith(array('BookVolumesPage_Catalog', 'Page'));
        } else {
            return $this->httpError(404, 'That catalog could not be found!');
        }
    }

}
