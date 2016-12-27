<?php


class ilBTIOContainer extends ActiveRecord {
  protected $id;
  protected $doCache;
  protected $cacheDuration;
  protected $inputForJob;
  protected $IOType;
  protected $data;
}