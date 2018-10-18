<?php

namespace Magento\MsiInventorySampleData\Model;


use Magento\Framework\App\ResourceConnection;


class MoveInventoryFromDefault
{

    protected $defaultSource = 'us_warehouse';
       /** @var ResourceConnection  */
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
       $this->resourceConnection = $resourceConnection;
    }

    public function transfer(){

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('inventory_source_item');
        $sql = "update " . $tableName . " set source_code = '".$this->defaultSource."' where source_code = 'default'";
        $connection->query($sql);
    }

}