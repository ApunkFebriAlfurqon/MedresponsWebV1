<?php

class Driver {
    public static function activeForAssignment(): array {
        return db()->fetchAll("SELECT * FROM drivers WHERE status != 'inactive' ORDER BY full_name");
    }
}
