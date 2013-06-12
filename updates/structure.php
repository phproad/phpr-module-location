<?php

$table = Db_Structure::table('location_countries');
	$table->primary_key('id');
	$table->column('code', db_varchar, 2);
	$table->column('name', db_varchar, 100)->index();
	$table->column('enabled', db_bool);
	$table->column('code_3', db_varchar, 3);
	$table->column('code_iso_numeric', db_varchar, 10);
