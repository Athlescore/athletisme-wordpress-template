<?php
/**
 * Plugin Name: Athlé Club
 * Description: Gestion des licenciés, espace entraîneur, jury et athlète.
 * Version: 0.1.0
 * Author: Athlé Sud 69
 * Text Domain: athle-club
 */
declare(strict_types=1);

if (!defined('ABSPATH')) exit;

define('ATHLE_CLUB_DIR', plugin_dir_path(__FILE__));

require_once ATHLE_CLUB_DIR . 'includes/roles.php';
require_once ATHLE_CLUB_DIR . 'includes/cpt-member.php';
