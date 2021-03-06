<?php
/**
 * Stats Base
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/


/**
 * EDD_Stats Class
 *
 * Base class for other stats classes
 *
 * Primarily for setting up dates and ranges
 *
 * @since 1.8
 */
class EDD_Stats {


	/**
	 * The start date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * @access public
	 * @since 1.8
	 */
	public $start_date;


	/**
	 * The end date for the period we're getting stats for
	 *
	 * Can be a timestamp, formatted date, date string (such as August 3, 2013),
	 * or a predefined date string, such as last_week or this_month
	 *
	 * Predefined date options are: today, yesterday, this_week, last_week, this_month, last_month
	 * this_quarter, last_quarter, this_year, last_year
	 *
	 * The end date is optional
	 *
	 * @access public
	 * @since 1.8
	 */
	public $end_date;

	/**
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct() { /* nothing here. Call get_sales() and get_earnings() directly */ }


	/**
	 * Get the predefined date periods permitted
	 *
	 * @access public
	 * @since 1.8
	 * @return array
	 */
	public function get_predefined_dates() {
		$predefined = array(
			'today'        => __( 'Today',        'edd' ),
			'yesterday'    => __( 'Yesterday',    'edd' ),
			'this_week'    => __( 'This Week',    'edd' ),
			'last_week'    => __( 'Last Week',    'edd' ),
			'this_month'   => __( 'This Month',   'edd' ),
			'last_month'   => __( 'Last Month',   'edd' ),
			'this_quarter' => __( 'This Quarter', 'edd' ),
			'last_quarter' => __( 'Last Quater',  'edd' ),
			'this_year'    => __( 'This Year',    'edd' ),
			'last_year'    => __( 'Last Year',    'edd' )
		);
		return apply_filters( 'edd_stats_predefined_dates', $predefined );
	}

	/**
	 * Setup the dates passed to our constructor.
	 *
	 * This calls the convert_date() member function to ensure the dates are formatted correctly
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function setup_dates( $_start_date = 'this_month', $_end_date = false ) {

		if( empty( $_start_date ) ) {
			$this->start_date = 'this_month';
		}

		$this->start_date = $_start_date;

		if( ! empty( $_end_date ) ) {
			$this->end_date = $_end_date;
		} else {
			$this->end_date = $this->start_date;
		}

		$this->start_date = $this->convert_date( $this->start_date );
		$this->end_date   = $this->convert_date( $this->end_date, true );

	}

	/**
	 * Converts a date to a timestamp
	 *
	 * @access public
	 * @since 1.8
	 * @return array|WP_Error If the date is invalid, a WP_Error object will be returned
	 */
	public function convert_date( $date, $end_date = false ) {

		$timestamp   = false;
		$minute      = 0;
		$hour        = 0;
		$day         = 1;
		$month       = date( 'n', current_time( 'timestamp' ) );
		$year        = date( 'Y', current_time( 'timestamp' ) );

		if ( array_key_exists( $date, $this->get_predefined_dates() ) ) {

			// This is a predefined date rate, such as last_week

			switch( $date ) {

				case 'this_month' :

					if( $end_date ) {

						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					}

					break;

				case 'last_month' :

					if( $month == 1 && ! $end_date ) {

						$month = 12;

					} else {

						$month = date( 'n', current_time( 'timestamp' ) ) - 1;
					}

					if( $end_date ) {

						$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );

					}

					break;

				case 'today' :

					$day = date( 'd', current_time( 'timestamp' ) );

					break;

				case 'yesterday' :

					$day = date( 'd', current_time( 'timestamp' ) ) - 1;
					if( $day < 1 ) {

						// Today is the first day of the month
						if( 1 == $month ) {

							$year -= 1; // Today is January 1, so skip back to December
							$month -= 1;
							$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						} else {

							$day = cal_days_in_month( CAL_GREGORIAN, $month, $year );

						}
					}

					break;

				case 'this_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) );

				 	if( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if( ! $end_date ) {

					 	// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 1;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'last_week' :

					$days_to_week_start = ( date( 'w', current_time( 'timestamp' ) ) - 1 ) *60*60*24;
				 	$today = date( 'd', current_time( 'timestamp' ) );

				 	if( $today < $days_to_week_start ) {

				 		if( $month > 1 ) {
					 		$month -= 1;
					 	} else {
					 		$month = 12;
					 	}

				 	}

					if( ! $end_date ) {

					 	// Getting the start day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' );

					} else {

						// Getting the end day

						$day = date( 'd', current_time( 'timestamp' ) - $days_to_week_start ) - 8;
						$day += get_option( 'start_of_week' ) + 6;

					}

					break;

				case 'this_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
						}

					} else {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$month = 12;
						}

					}

					break;

				case 'last_quarter' :

					$month_now = date( 'n', current_time( 'timestamp' ) );

					if ( $month_now <= 3 ) {

						if( ! $end_date ) {
							$month = 10;
						} else {
							$year -= 1;
							$month = 12;
						}

					} else if ( $month_now <= 6 ) {

						if( ! $end_date ) {
							$month = 1;
						} else {
							$month = 3;
						}

					} else if ( $month_now <= 9 ) {

						if( ! $end_date ) {
							$month = 4;
						} else {
							$month = 6;
						}

					} else {

						if( ! $end_date ) {
							$month = 7;
						} else {
							$month = 9;
						}

					}

					break;

				case 'this_year' :

					if( ! $end_date ) {
						$month = 1;
					} else {
						$month = 12;
					}

					break;

				case 'last_year' :

					$year -= 1;
					if( ! $end_date ) {
						$month = 1;
					} else {
						$month = 12;
					}

				break;

			}


		} else if( is_int( $date ) ) {

			// return $date unchanged since it is a timestamp
			$timestamp = true;

		} else if( false !== strtotime( $date ) ) {

			$timestamp = true;
			$date      = strtotime( $date, current_time( 'timestamp' ) );

		} else {

			return new WP_Error( 'invalid_date', __( 'Improper date provided.', 'edd' ) );

		}

		if( ! is_wp_error( $date ) && ! $timestamp ) {

			// Create an exact timestamp
			$date = mktime( $hour, $minute, 0, $month, $day, $year );

		}

		return apply_filters( 'edd_stats_date', $date, $end_date, $this );

	}

	/**
	 * Modifies the WHERE flag for payment counts
	 *
	 * @access public
	 * @since 1.8
	 * @return string
	 */
	public function count_where( $where = '' ) {
		// Only get payments in our date range

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND p.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
			$end_where = " AND p.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

	/**
	 * Modifies the WHERE flag for payment queries
	 *
	 * @access public
	 * @since 1.8
	 * @return string
	 */
	public function payments_where( $where = '' ) {

		global $wpdb;

		$start_where = '';
		$end_where   = '';

		if( $this->start_date ) {
			$start_date  = date( 'Y-m-d 00:00:00', $this->start_date );
			$start_where = " AND $wpdb->posts.post_date >= '{$start_date}'";
		}

		if( $this->end_date ) {
			$end_date  = date( 'Y-m-d 23:59:59', $this->end_date );
			$end_where = " AND $wpdb->posts.post_date <= '{$end_date}'";
		}

		$where .= "{$start_where}{$end_where}";

		return $where;
	}

}