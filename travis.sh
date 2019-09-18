# ENV
SPLINT_VERSION="0.0.8"
VENDOR="francis94c"
PACKAGE="refactor-ci"

created=false

for entry in ./*
do
	echo ${entry}
	if [ $created = false ]; then
		mkdir -p travis-splint-${SPLINT_VERSION}/application/splints/${VENDOR}/${PACKAGE}
		$created = true
	fi
	if [ "x$entry" != "x./phpunit.xml" ] && [ "x$entry" != "x./travis.sh" ]; then
		cp -r $entry travis-splint-${SPLINT_VERSION}/application/splints/${VENDOR}/${PACKAGE}/
		rm -rf $entry
	fi
done

wget https://github.com/splintci/travis-splint/archive/v${SPLINT_VERSION}.tar.gz -O - | tar xz
