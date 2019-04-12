<?php

namespace Alnv\CatalogManagerAuctionExtensionBundle\Contao;


use Alnv\CatalogManagerAuctionExtensionBundle\Views\ListView;
use Alnv\CatalogManagerAuctionExtensionBundle\Views\FormView;


class Inserttag {


    public function parse( $strInserttag ) {

        $arrInserttag = explode( '::',  $strInserttag );

        if ( $arrInserttag[0] == 'bid_form_view' ) {

            $strTablename = $arrInserttag[1];
            $strId = $arrInserttag[2];

            if ( !$strTablename || !$strId ) {

                return '';
            }

            $objFormView = new FormView( $strTablename, $strId );

            return $objFormView->parse();
        }


        if ( $arrInserttag[0] == 'bid_list_view' ) {

            $strTablename = $arrInserttag[1];
            $strId = $arrInserttag[2];

            if ( !$strTablename || !$strId ) {

                return '';
            }

            $objListView = new ListView( $strTablename, $strId );

            return $objListView->parse();
        }

        if ( $arrInserttag[0] == 'bid_user' ) {

            $objDatabase = \Database::getInstance();

            if ( !FE_USER_LOGGED_IN || !$objDatabase->tableExists( 'cm_offers' ) ) {

                return '';
            }

            $arrReturn = [];
            $objUser = \FrontendUser::getInstance();
            $objEntities = $objDatabase->prepare( 'SELECT * FROM cm_offers WHERE member = ?' )->execute( $objUser->id );

            if ( !$objEntities->numRows ) {

                return '';
            }

            while ( $objEntities->next() ) {

                if ( $objEntities->offer_to && !in_array( $objEntities->offer_to, $arrReturn ) ) {

                    $arrReturn[] = $objEntities->offer_to;
                }
            }

            return implode( ',', $arrReturn );
        }

        return false;
    }
}