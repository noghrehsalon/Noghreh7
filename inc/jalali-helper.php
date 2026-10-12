<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * تبدیل تاریخ میلادی به شمسی (الگوریتم استاندارد و رایج، بدون نیاز به کتابخانه خارجی)
 *
 * @return array [سال, ماه, روز] شمسی
 */
function noghre_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + intdiv( ( $gy2 + 3 ), 4 ) - intdiv( ( $gy2 + 99 ), 100 )
		+ intdiv( ( $gy2 + 399 ), 400 ) + $gd + $g_d_m[ $gm - 1 ];

	$jy = -1595 + ( 33 * intdiv( $days, 12053 ) );
	$days %= 12053;
	$jy += 4 * intdiv( $days, 1461 );
	$days %= 1461;

	if ( $days > 365 ) {
		$jy += intdiv( ( $days - 1 ), 365 );
		$days = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jm = 1 + intdiv( $days, 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + intdiv( ( $days - 186 ), 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jy, $jm, $jd );
}

function noghre_jalali_month_name( $m ) {
	$months = array( '', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	return $months[ (int) $m ] ?? '';
}

function noghre_jalali_weekday_name( $timestamp ) {
	$w    = (int) date( 'w', $timestamp ); // 0=یکشنبه ... 6=شنبه
	$days = array( 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه' );
	return $days[ $w ];
}

function noghre_to_persian_digits( $str ) {
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, (string) $str );
}

/**
 * تبدیل تاریخ شمسی به میلادی (الگوریتم معکوس استاندارد)
 * @return array [سال, ماه, روز] میلادی
 */
function noghre_jalali_to_gregorian( $jy, $jm, $jd ) {
	$jy += 1595;
	$days = -355668 + ( 365 * $jy ) + ( intdiv( $jy, 33 ) * 8 ) + intdiv( ( $jy % 33 + 3 ), 4 ) + $jd + ( ( $jm < 7 ) ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );
	$gy = 400 * intdiv( $days, 146097 );
	$days %= 146097;
	if ( $days > 36524 ) {
		$gy += 100 * intdiv( --$days, 36524 );
		$days %= 36524;
		if ( $days >= 365 ) $days++;
	}
	$gy += 4 * intdiv( $days, 1461 );
	$days %= 1461;
	if ( $days > 365 ) {
		$gy += intdiv( ( $days - 1 ), 365 );
		$days = ( $days - 1 ) % 365;
	}
	$gd = $days + 1;
	$g_days_in_month = array( 31, ( ( $gy % 4 === 0 && $gy % 100 !== 0 ) || ( $gy % 400 === 0 ) ) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
	$gm = 0;
	for ( $i = 0; $i < 12; $i++ ) {
		if ( $gd <= $g_days_in_month[ $i ] ) { $gm = $i + 1; break; }
		$gd -= $g_days_in_month[ $i ];
	}
	return array( $gy, $gm, $gd );
}

/**
 * تبدیل رشته تاریخ شمسی (Y-m-d با اعداد فارسی یا انگلیسی) به رشته میلادی Y-m-d
 */
function noghre_jalali_string_to_gregorian_string( $jalali_str ) {
	$jalali_str = str_replace( array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ), array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), $jalali_str );
	if ( ! preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', trim( $jalali_str ), $m ) ) return '';
	list( , $jy, $jm, $jd ) = $m;
	list( $gy, $gm, $gd ) = noghre_jalali_to_gregorian( (int) $jy, (int) $jm, (int) $jd );
	return sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );
}

/**
 * تبدیل رشته تاریخ میلادی Y-m-d به رشته شمسی Y-m-d (اعداد انگلیسی، برای فیلدهای فرم)
 */
function noghre_gregorian_string_to_jalali_string( $gregorian_str ) {
	if ( ! preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', trim( (string) $gregorian_str ), $m ) ) return '';
	list( , $gy, $gm, $gd ) = $m;
	list( $jy, $jm, $jd ) = noghre_gregorian_to_jalali( (int) $gy, (int) $gm, (int) $gd );
	return sprintf( '%04d-%02d-%02d', $jy, $jm, $jd );
}

/**
 * فرمت‌دهی یک timestamp به تاریخ شمسی.
 * توکن‌های پشتیبانی‌شده: Y (سال) F (نام ماه) m (شماره ماه دورقمی) n (شماره ماه) j (روز) d (روز دورقمی) l (نام روز هفته) H:i (ساعت:دقیقه)
 */
function noghre_jalali_date( $timestamp, $format = 'j F Y' ) {
	$timestamp = (int) $timestamp;
	list( $jy, $jm, $jd ) = noghre_gregorian_to_jalali(
		(int) date( 'Y', $timestamp ),
		(int) date( 'n', $timestamp ),
		(int) date( 'j', $timestamp )
	);

	$replacements = array(
		'Y' => $jy,
		'F' => noghre_jalali_month_name( $jm ),
		'm' => str_pad( $jm, 2, '0', STR_PAD_LEFT ),
		'n' => $jm,
		'j' => $jd,
		'd' => str_pad( $jd, 2, '0', STR_PAD_LEFT ),
		'l' => noghre_jalali_weekday_name( $timestamp ),
		'H' => date( 'H', $timestamp ),
		'i' => date( 'i', $timestamp ),
	);

	$out = strtr( $format, $replacements );
	return noghre_to_persian_digits( $out );
}
