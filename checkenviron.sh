#!/usr/bin/env bash
modules=( gd pdo_sqlite exif imagick)
for i in "${modules[@]}"
do
	:
	ok=$(php -m | grep -i $i)
	if [ "$i" == "$ok" ]; then
    	echo "Passed"
	else
		err="$i missing"
		echo "$err"
	fi
done

