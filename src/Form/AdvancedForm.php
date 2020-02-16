<?php

/*
 * Copyright Daniel Berthereau 2018-2020
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace Search\Form;

use Omeka\Api\Manager;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;

class AdvancedForm extends Form
{
    /**
     * @var Manager
     */
    protected $apiManager;

    protected $formElementManager;

    public function init()
    {
        $this
            ->add([
                'name' => 'q',
                'type' => Element\Text::class,
                'options' => [
                    'label' => 'Search', // @translate
                ],
                'attributes' => [
                    'placeholder' => 'Search', // @translate
                ],
            ])

            ->add($this->itemSetFieldset())
            ->add($this->textFieldset())

            ->add([
                'name' => 'submit',
                'type' => Element\Submit::class,
                'attributes' => [
                    'value' => 'Submit', // @translate
                    'type' => 'submit',
                ],
            ])
        ;

        $inputFilter = $this->getInputFilter();
        $inputFilter
            ->get('itemSet')->add([
                'name' => 'ids',
                'required' => false,
            ])
        ;
    }

    public function setApiManager(Manager $apiManager)
    {
        $this->apiManager = $apiManager;
        return $this;
    }

    public function getApiManager()
    {
        return $this->apiManager;
    }

    public function setFormElementManager($formElementManager)
    {
        $this->formElementManager = $formElementManager;
        return $this;
    }

    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    protected function itemSetFieldset()
    {
        $fieldset = new Fieldset('itemSet');
        $fieldset->add([
            'name' => 'ids',
            'type' => Element\MultiCheckbox::class,
            'options' => [
                // For normal users, it is not "item sets", but "collections".
                'label' => 'Collections', // @translate
                'value_options' => $this->getItemSetsOptions(),
            ],
        ]);
        return $fieldset;
    }

    protected function textFieldset()
    {
        $fieldset = new Fieldset('text');
        $filterFieldset = $this->getFilterFieldset();
        if ($filterFieldset->count()) {
            $fieldset
                ->add([
                    'name' => 'filters',
                    'type' => Element\Collection::class,
                    'options' => [
                        'label' => 'Filters', // @translate
                        'count' => 2,
                        'should_create_template' => true,
                        'allow_add' => true,
                        'target_element' => $filterFieldset,
                        'required' => false,
                    ],
                ])
            ;
        }
        return $fieldset;
    }

    protected function getItemSetsOptions()
    {
        $api = $this->getApiManager();

        $itemSets = $api->search('item_sets', [
            'is_public' => true,
        ])->getContent();
        $options = [];
        foreach ($itemSets as $itemSet) {
            $options[$itemSet->id()] = $itemSet->displayTitle();
        }

        return $options;
    }

    protected function getFilterFieldset()
    {
        $options = $this->getOptions();
        return $this->getForm(FilterFieldset::class, $options);
    }

    protected function getForm($name, $options)
    {
        return $this->getFormElementManager()
            ->get($name, $options);
    }
}