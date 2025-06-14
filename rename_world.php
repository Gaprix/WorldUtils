<?php

require_once __DIR__ . '/vendor/autoload.php';

use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;

if($argc < 3){
    fwrite(STDERR, "Usage: <path_to_level.dat> <new_world_name>\n");
    exit(1);
}

$path = $argv[1];
$newName = $argv[2];
if(!is_file($path)){
    fwrite(STDERR, "File not found: $path\n");
    exit(1);
}

$raw = file_get_contents($path);
if($raw === false || strlen($raw) <= 8){
    fwrite(STDERR, "Invalid level.dat format or empty file\n");
    exit(1);
}

$header = substr($raw, 0, 8);
$storageVersion = unpack("V", substr($header, 0, 4))[1];

$nbt = new LittleEndianNbtSerializer();
try {
    $compound = $nbt->read(substr($raw, 8))->mustGetCompoundTag();
} catch (\Throwable $e) {
    fwrite(STDERR, "Error reading NBT: ".$e->getMessage()."\n");
    exit(1);
}

$compound->setString("LevelName", $newName);

$buffer = $nbt->write(new TreeRoot($compound));
$versionBytes = pack('V', $storageVersion);
$lengthBytes = pack('V', strlen($buffer));
file_put_contents($path, $versionBytes . $lengthBytes . $buffer);
