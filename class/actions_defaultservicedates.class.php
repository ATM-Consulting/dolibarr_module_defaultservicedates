<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_defaultservicedates.class.php
 * \ingroup defaultservicedates
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsdefaultservicedates
 */
class Actionsdefaultservicedates
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Overloading the formCreateProductOptions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formCreateProductOptions($parameters, &$object, &$action, $hookmanager)
	{
        global $conf;

	    // This behaviour will be standard in Dolibarr 11 : don't handle it then
		if((float) DOL_VERSION < 11 && ! empty($conf->service->enabled) && $this->_contextIsHandled($parameters) && ! empty($object->lines))
		{
			$date_start=dol_mktime(GETPOST('date_starthour'), GETPOST('date_startmin'), 0, GETPOST('date_startmonth'), GETPOST('date_startday'), GETPOST('date_startyear'));
			$date_end=dol_mktime(GETPOST('date_starthour'), GETPOST('date_startmin'), 0, GETPOST('date_endmonth'), GETPOST('date_endday'), GETPOST('date_endyear'));

			$found = false;

			for($i = count($object->lines) - 1; $i >= 0; $i--)
            {
                $line = $object->lines[$i];

                if($line->product_type == Product::TYPE_SERVICE && (! empty($line->date_start) || ! empty($line->date_end)))
                {
	                $date_start = $line->date_start;
	                $date_end = $line->date_end;

                	$found = true;
                	break;
                }
            }

			if(! $found)
			{
				return 0;
			}


			global $langs;

			$langs->load('defaultservicedates@defaultservicedates');
?>
			<script>
                function defaultservicedates_filldates()
                {
                    $('#date_start').val("<?php echo dol_escape_js(dol_print_date($date_start, '%d/%m/%Y')); ?>").trigger('change');
                    $('#date_end').val("<?php echo dol_escape_js(dol_print_date($date_end, '%d/%m/%Y')); ?>").trigger('change');

                    return false;
                }

				$(document).ready(function()
                {
                    var prefillLink = $('<a>').prop('href', '#').html('<?php echo dol_escape_js($langs->trans('FillWithLastServiceDates')); ?>');
                    prefillLink.click(defaultservicedates_filldates);
                    var prefillSpan = $('<span class="small"></span>').append('(', prefillLink, ')');
                    $('#trlinefordates td:first-child').append(' ', prefillSpan);
				});
			</script>
<?php

            return 0;
		}
	}


	function _contextIsHandled($parameters)
	{
		$TCurrentContexts = explode(':', $parameters['context']);
		$THandledContexts = array(
			'propalcard'
            , 'ordercard'
            , 'invoicecard'
            , 'contractcard'
            , 'invoicereccard'
            , 'supplier_proposalcard'
            , 'ordersuppliercard'
            , 'invoicesuppliercard'
        );

		$TDiff = array_intersect($THandledContexts, $TCurrentContexts);

		return ! empty($TDiff);
	}
}
