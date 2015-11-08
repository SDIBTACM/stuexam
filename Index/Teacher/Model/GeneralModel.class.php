<?php
/**
 * drunk , fix later
 * Created by PhpStorm.
 * User: jiaying
 * Datetime: 15/11/8 17:26
 */

namespace Teacher\Model;


abstract class GeneralModel
{

    abstract protected function getDao();

    abstract protected function getTableName();

}