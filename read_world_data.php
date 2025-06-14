<?php

require_once __DIR__ . '/vendor/autoload.php';

use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

if ($argc < 2) {
    fwrite(STDERR, "Usage: <path_to_level.dat>\n");
    exit(1);
}

$path = $argv[1];
if (!is_file($path)) {
    fwrite(STDERR, "File not found: $path\n");
    exit(1);
}

$raw = file_get_contents($path);
if ($raw === false || strlen($raw) <= 8) {
    fwrite(STDERR, "Invalid or empty level.dat\n");
    exit(1);
}

$nbt = new LittleEndianNbtSerializer();
try {
    $compound = $nbt->read(substr($raw, 8))->mustGetCompoundTag();
} catch (Throwable $e) {
    fwrite(STDERR, "Error reading NBT: " . $e->getMessage() . "\n");
    exit(1);
}

function tagToArray($tag) {
    if ($tag instanceof CompoundTag) {
        $result = [];
        foreach ($tag->getValue() as $name => $child) {
            $result[$name] = tagToArray($child);
        }
        return $result;
    } elseif ($tag instanceof ListTag) {
        $result = [];
        foreach ($tag->getValue() as $child) {
            $result[] = tagToArray($child);
        }
        return $result;
    } else {
        return $tag->getValue();
    }
}

$data = tagToArray($compound);
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
