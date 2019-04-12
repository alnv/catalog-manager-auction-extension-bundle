<?php

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'Alnv\CatalogManagerBidExtensionBundle\Contao\Inserttag', 'parse' ];

array_insert( $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['catalog_manager'], 0, [

    'success_bid'   => [

        'recipients' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_replyTo' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_sender_name' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_recipient_cc' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_recipient_bcc' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_sender_address' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_subject' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'attachment_tokens' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'file_name' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'file_content' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_text' => [ 'admin_email', 'offer_object_*', 'member_*' ],
        'email_html' => [ 'admin_email', 'offer_object_*', 'member_*' ]
    ]
]);