<?php
namespace ILIAS\App\Infrasctrutre\Repository;

interface Repository {

	function doFind(array $ids): object;


	function doFindByFields(array $fields): array;


	function doSave($entity): void;


	function doDelete($entity): void;
}