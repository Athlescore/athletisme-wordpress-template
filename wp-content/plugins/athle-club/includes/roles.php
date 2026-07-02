<?php
declare(strict_types=1);

register_activation_hook(ATHLE_CLUB_DIR . 'athle-club.php', 'athle_club_add_roles');
register_deactivation_hook(ATHLE_CLUB_DIR . 'athle-club.php', 'athle_club_remove_roles');

function athle_club_add_roles(): void
{
    add_role('athle_coach', 'Entraîneur', [
        'read' => true,
    ]);

    add_role('athle_jury', 'Jury', [
        'read' => true,
    ]);

    add_role('athle_licencie', 'Licencié', [
        'read' => true,
    ]);
}

function athle_club_remove_roles(): void
{
    remove_role('athle_coach');
    remove_role('athle_jury');
    remove_role('athle_licencie');
}
