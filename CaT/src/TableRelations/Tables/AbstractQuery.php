<?php

interface AbstractQuery {
	public function requested();
	public function root();
	public function dependent();
	public function having();
	public function groupedBy();
}