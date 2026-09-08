<?php

declare(strict_types = 1);

namespace Shinya\PhpRadser\Contracts;

/**
 * implemented by attribute classes carried inside a Vendor-Specific (26) attribute;
 * distinguishing vendor from standard attributes is a type check (instanceof/is_a) against this
 * interface instead of a nullable vendorId() on every Attribute.
 */
interface VendorAttribute
{
	/**
	 * SMI enterprise number of the vendor that owns this attribute.
	 */
	public static function vendorId(): int;
}
