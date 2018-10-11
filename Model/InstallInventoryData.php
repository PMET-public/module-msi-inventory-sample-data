<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MsInventorySampleData\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;


/**
 * Class InstallInventory
 * @package Magento\MsiLumaInventorySampleData\Setup\Patch\Data
 */
class InstallInventoryData
{

     /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /** @var SearchCriteriaBuilder  */
    protected $searchCriteriaBuilder;

    /** @var SourceItemRepositoryInterface  */
    protected $sourceItemRepository;

    /** @var $sourceItemInterfaceFactory */
    protected $sourceItemInterface;

    /** @var SourceItemsDeleteInterface  */
    protected $sourceItemsDelete;

    /**
     * InstallInventoryData constructor.
     * @param SampleDataContext $sampleDataContext
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SourceItemInterfaceFactory $sourceItemInterfaceFactory
     * @param SourceItemsDeleteInterface $sourceItemsDelete
     */

    public function __construct(
        SampleDataContext $sampleDataContext,
        ModuleDataSetupInterface $moduleDataSetup,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        SourceItemInterfaceFactory $sourceItemInterfaceFactory,
        SourceItemsDeleteInterface $sourceItemsDelete
       )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemInterface = $sourceItemInterfaceFactory;
        $this->sourceItemsDelete = $sourceItemsDelete;
    }


    /**
     * @param array $fixtures
     */
    public function addInventory(array $fixtures): void
    {
        foreach ($fixtures as $fileName) {
            $fileName = $this->fixtureManager->getFixture($fileName);
            if (!file_exists($fileName)) {
                continue;
            }

            $rows = $this->csvReader->getData($fileName);
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                //if sku/source has already been defined, skip
                $search = $this->searchCriteriaBuilder
                    ->addFilter(SourceItemInterface::SOURCE_CODE,$data['source_code'],'eq')
                    ->addFilter(SourceItemInterface::SKU, $data['sku'], 'eq')
                    ->create();
                $sourceItemSearch = $this->sourceItemRepository->getList($search)->getTotalCount();
                if($sourceItemSearch==0) {
                    $sourceItem = $this->sourceItemInterface->create();
                    $sourceItem->setSku($data['sku']);
                    $sourceItem->setSourceCode($data['source_code']);
                    $sourceItem->setQuantity($data['quantity']);
                    $sourceItem->setStatus($data['status']);
                    $sourceItem->save();
                }
                //remove default inventory
                $search = $this->searchCriteriaBuilder
                    ->addFilter(SourceItemInterface::SOURCE_CODE,'default','eq')
                    ->addFilter(SourceItemInterface::SKU, $data['sku'], 'eq')
                    ->create();
                $defaultItems = $this->sourceItemRepository->getList($search)->getItems();
                if(count($defaultItems)){
                    $this->sourceItemsDelete->execute($defaultItems);
                }
            }
        }
    }

}