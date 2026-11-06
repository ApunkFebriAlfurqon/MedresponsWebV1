<?php

class Hospital {
    public static function activeForSelection(): array {
        return db()->fetchAll("SELECT * FROM hospitals WHERE status = 'active' ORDER BY name");
    }
}
