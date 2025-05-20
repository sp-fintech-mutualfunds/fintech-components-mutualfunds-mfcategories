<?php

namespace Apps\Fintech\Components\Mf\Categories;

use Apps\Fintech\Packages\Adminltetags\Traits\DynamicTable;
use Apps\Fintech\Packages\Mf\Categories\MfCategories;
use System\Base\BaseComponent;

class CategoriesComponent extends BaseComponent
{
    use DynamicTable;

    protected $categoriesPackage;

    protected $categories = [];

    public function initialize()
    {
        $this->categoriesPackage = $this->usePackage(MfCategories::class);
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $category = $this->categoriesPackage->getById((int) $this->getData()['id']);

                if (!$category) {
                    return $this->throwIdNotFound();
                }

                $this->view->category = $category;
            }

            $this->view->pick('categories/view');

            return;
        }

        $controlActions =
            [
                // 'disableActionsForIds'  => [1],
                'actionsToEnable'       =>
                [
                    'edit'      => 'mf/categories',
                    'remove'    => 'mf/categories/remove'
                ]
            ];

        $replaceColumns =
            function ($dataArr) {
                if ($dataArr && is_array($dataArr) && count($dataArr) > 0) {
                    return $this->replaceColumns($dataArr);
                }

                return $dataArr;
            };

        $this->generateDTContent(
            $this->categoriesPackage,
            'mf/categories/view',
            null,
            ['name', 'parent_id'],
            true,
            ['name', 'parent_id'],
            $controlActions,
            null,
            $replaceColumns,
            'name'
        );

        $this->view->pick('categories/list');
    }

    protected function replaceColumns($dataArr)
    {
        foreach ($dataArr as $dataKey => &$data) {
            $data = $this->formatParent($dataKey, $data);
        }

        return $dataArr;
    }

    protected function formatParent($rowId, $data)
    {
        if ($data['parent_id']) {
            if (!isset($this->categories[$data['parent_id']])) {
                $category = $this->categoriesPackage->getById((int) $data['parent_id']);

                if ($category) {
                    $this->categories[$data['parent_id']] = $category['name'] . ' (' . $data['parent_id'] . ')';
                } else {
                    $this->categories[$data['parent_id']] = $data['parent_id'];
                }
            }

            $data['parent_id'] = $this->categories[$data['parent_id']];
        }

        return $data;
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        //$this->package->add{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        //$this->package->update{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }

    /**
     * @acl(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        //$this->package->remove{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }

    public function calculateCategoriesVarianceAction()
    {
        $this->requestIsPost();

        if (!isset($this->postData()['mainCategory']) ||
            !isset($this->postData()['withCategory'])
        ) {
            $this->addResponse('Please provide main and with categories', 1);

            return false;
        }

        $this->categoriesPackage->calculateCategoriesVariance($this->postData()['mainCategory'], $this->postData()['withCategory']);

        $this->addResponse(
            $this->categoriesPackage->packagesData->responseMessage,
            $this->categoriesPackage->packagesData->responseCode,
            $this->categoriesPackage->packagesData->responseData ?? []
        );
    }
}