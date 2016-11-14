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
 * @version 1.0, Sep 29, 2016 - 3:26:27 PM
 */
class AuthorField
        extends TagField {

    /**
     * @var string
     */
    protected $titleFunction = 'Title';

    /**
     * @return string
     */
    public function getTitleFunction() {
        return $this->titleFunction;
    }

    /**
     * @param string $titleField
     *
     * @return $this
     */
    public function setTitleFunction($titleField) {
        $this->titleFunction = $titleField;

        return $this;
    }

    /**
     * @return ArrayList
     */
    protected function getOptions() {
        $options = ArrayList::create();

        $source = $this->getSource();

        if (!$source) {
            $source = new ArrayList();
        }

        $dataClass = $source->dataClass();

        $values = $this->Value();

        // Mark selected tags while still returning a full list of possible options
        $ids = array(); // empty fallback array for comparing
        $values = $this->Value();
        if ($values) {
            // @TODO conversion from array to DataList to array...(?)
            if (is_array($values)) {
                $values = DataList::create($dataClass)->filter('ID', $values);
            }
            $ids = $values->column('ID');
        }

        $titleField = $this->getTitleFunction();

        foreach ($source as $object) {
            $options->push(
                    ArrayData::create(array(
                        'Title' => $object->$titleField(),
                        'Value' => $object->ID,
                        'Selected' => in_array($object->ID, $ids),
                    ))
            );
        }

        return $options;
    }

}