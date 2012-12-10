<?php
    global $wpdb;

    $table_numbers = $wpdb->prefix.'numbers';
    $table_busy = $wpdb->prefix.'busy';
    $table_orders = $wpdb->prefix.'orders';
    $table_clients = $wpdb->prefix.'clients';
    $table_diapasones = $wpdb->prefix.'diapasones';
    $table_numbers2diapasones = $wpdb->prefix.'numbers2diapasones';
    $table_number_groups = $wpdb->prefix.'number_groups';
    $table_hotel_fotos = $wpdb->prefix.'hotel_foto';

    $sql = "DROP TABLE IF EXISTS $table_numbers";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS $table_hotel_fotos";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS $table_busy";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_orders";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_clients";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_diapasones";
    $wpdb->query($sql);
    
    $sql = "DROP TABLE IF EXISTS $table_numbers2diapasones";
    $wpdb->query($sql);

    $sql = "DROP TABLE IF EXISTS $table_number_groups";
    $wpdb->query($sql);
    
?>