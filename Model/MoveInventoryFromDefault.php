<?php

namespace Magento\MsiInventorySampleData\Model;


use Magento\Framework\App\ResourceConnection;


class MoveInventoryFromDefault
{

    /** @var ResourceConnection  */
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
       $this->resourceConnection = $resourceConnection;
    }

    public function transfer(string $transferTo){

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('inventory_source_item');
        $sql = "update " . $tableName . " set source_code = '".$transferTo."' where source_code = 'default'";
        $connection->query($sql);
    }

}