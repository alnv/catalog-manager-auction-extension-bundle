<?php

namespace Alnv\CatalogManagerAuctionExtensionBundle\Views;


class ListView {


    protected $strTable = null;
    protected $strId = null;


    public function __construct( $strTablename, $strId ) {


        $this->strTable = $strTablename;
        $this->strId = $strId;
    }

    public function parse() {

        $objTemplate = new \FrontendTemplate( 'bid_view_list' );

        $objTemplate->id = $this->strId;
        $objTemplate->table = $this->strTable;

        return $objTemplate->parse();
    }
}