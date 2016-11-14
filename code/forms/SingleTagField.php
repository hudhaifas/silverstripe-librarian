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
class SingleTagField
        extends AuthorField {

    /**
     * {@inheritdoc}
     */
    public function setValue($value, $source = null) {
        $debug = 'Source: ' . $source . '<br />';
        $debug .= 'Value: ' . $value . '<br />';

        if ($source instanceof DataObject) {
            $name = $this->getName();

            $debug .= 'Name: ' . $name . '<br />';

            if ($source->hasMethod($name)) {
                $debug .= 'Class: ' . get_class($source->$name()) . '<br />';
                $value = $source->$name()->Name;
                $debug .= 'Values: ' . $value . '<br />';
            }
            die($debug);
        } elseif ($value instanceof SS_List) {
            $value = $value->column('ID');
        }

        if (!is_array($value)) {
            return parent::setValue($value);
        }

        return parent::setValue(array_filter($value));
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
//        die('Values: ' . $values);
        // Mark selected tag while still returning a full list of possible options
        $id = array(); // empty fallback array for comparing
        $values = $this->Value();
//                die('Class: ' . get_class($values) . '<br />');

        if ($values) {
            // @TODO conversion from array to DataList to array...(?)
            if (is_array($values)) {
                $values = DataList::create($dataClass)->filter('ID', $values);
            }
//                die('Values: ' . $values);
//            $ids = $values->column('ID');
        }

        $titleField = $this->getTitleFunction();

        foreach ($source as $object) {
            $options->push(
                    ArrayData::create(array(
                        'Title' => $object->$titleField(),
                        'Value' => $object->ID,
                        'Selected' => in_array($object->ID, $id),
                    ))
            );
        }

        return $options;
    }

    public function getIsMultiple() {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function saveInto(DataObjectInterface $record) {
        parent::saveInto($record);

        $name = $this->getName();
        $titleField = $this->getTitleField();

        $source = $this->getSource();

        $values = $this->Value();

        if (!$values) {
            $values = array();
        }

        if (empty($record) || empty($source) || empty($titleField)) {
            return;
        }

        if (!$record->hasMethod($name)) {
            throw new Exception(
            sprintf("%s does not have a %s method", get_class($record), $name)
            );
        }

        $relation = $record->$name();

        foreach ($values as $i => $value) {
            if (!is_numeric($value)) {
                if (!$this->getCanCreate()) {
                    unset($values[$i]);
                    continue;
                }

                // Get or create record
                $record = $this->getOrCreateTag($value);
                $values[$i] = $record->ID;
            }
        }

        if ($values instanceof SS_List) {
            $values = iterator_to_array($values);
        }

        $relation->setByIDList(array_filter($values));
    }

}