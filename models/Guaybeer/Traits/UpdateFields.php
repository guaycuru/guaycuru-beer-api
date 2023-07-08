<?php

namespace Guaybeer\Traits;

trait UpdateFields {
	public function UpdateFields(array $fields): bool {
		$updated = false;

		foreach($fields as $name => $value) {
			if (!empty($value) && (!isset($this->$name) || $this->$name !== $value)) {
				$this->$name = $value;
				$updated = true;
			}
		}

		return $updated;
	}
}
