<?php

namespace Alnv\CatalogManagerAuctionExtensionBundle\Views;


class FormView {


    protected $strTable = null;
    protected $strId = null;


    public function __construct( $strTablename, $strId ) {


        $this->strTable = $strTablename;
        $this->strId = $strId;
    }

    public function parse() {

        $objTemplate = new \FrontendTemplate( 'auction_view_form' );

        $objTemplate->id = $this->strId;
        $objTemplate->table = $this->strTable;

        return $objTemplate->parse();
    }
}