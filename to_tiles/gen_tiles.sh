#!/bin/bash

trap clean_tmp EXIT
# fin d'eviter tout problème de locales en reste en C de base.

set -e
export LANG=C

# pour éliminer systématiquement les fichier temporaires créés ic
function clean_tmp() {
    if [ -n "$wfname" ]; then
	rm $wfname
    fi
    if [ -n "$tmp_file" ]; then
	rm $tmp_file
    fi
}

test_mode=false
memory_limit=256
crop_x=256
crop_y=256
min_scale=0
max_scale=8
usage="$0 [-x <x_tile_size>] [-y <y_tile_size>] [-p <prefix_result>] [-t] [-h] [-m <min_zoom>] [-M <max_zoom>] <image_to_convert>\n   example: $0 -r test_res"

if ! which anytopnm pnmscale convert > /dev/null; then
    echo "il faut installer les paquets netpbm et imageMagick pour utiliser ce script !"
fi

while getopts m:M:x:y:p:ht prs
 do
 case $prs in
    t)        test_mode=true;;
    x)        crop_x=$OPTARG;;
    y)        crop_y=$OPTARG;;
    m)        min_scale=$OPTARG;;
    M)        max_scale=$OPTARG;;
    p)        prefix=$OPTARG;;
    \? | h)   echo -e $usage
              exit 2;;
 esac
done
shift `expr $OPTIND - 1`

if [ -z "$1" ]; then
    echo -e "usage :\n$usage"
    exit 1
elif [ ! -f "$1" ]; then
    echo -e "le paramètre $1 ne correspond pas à un nom de fichier !"
    exit 1
fi

fname=$1
dir=$(dirname $fname)

if [ -z "$prefix" ]; then
    prefix=$(basename $1|sed 's/\..*$//')
fi

wfname=$(mktemp ${prefix}_XXXX.pnm)
if ! $test_mode; then
    anytopnm $fname > $wfname
else
    echo "anytopnm $fname > $wfname"
fi

echo "préfixe : "$prefix

tmp_file=$(mktemp)

for ((z=$min_scale; z <= $max_scale; z++))
do
    fprefix=${prefix}_00$z
    printf -v ratio %1.4lf $(echo "1 / (2^$z)" | bc -l)
    echo génération du ratio $ratio
    zwfname=$tmp_file

    if $test_mode; then
	if [ $ratio = 1.0000 ]; then
	    zwfname=$wfname
	else
	    echo "pnmscale $ratio $wfname > $zwfname"
	fi
	echo convert $zwfname \
	    -limit memory $memory_limit \
            -crop ${crop_x}x${crop_x} \
            -set filename:tile "%[fx:page.x/${crop_x}]_%[fx:page.y/${crop_y}]" \
            +repage +adjoin "${fprefix}_%[filename:tile].jpg"
    else
	if [ $ratio = 1.0000 ]; then
	    zwfname=$wfname
	else
	    if ! pnmscale $ratio $wfname > $zwfname; then
		echo "operation 'pnmscale $ratio $wfname > $zwfname' en erreur"
		exit 1
	    fi
	fi
	if convert $zwfname \
	    -limit memory $memory_limit \
            -crop ${crop_x}x${crop_x} \
            -set filename:tile "%[fx:page.x/${crop_x}]_%[fx:page.y/${crop_y}]" \
            +repage +adjoin "${fprefix}_%[filename:tile].jpg"; then
	    echo "Nombre des fichiers produits :" $(ls -la ${fprefix}_*| wc -l)
	else
	    echo "operation 'convert' en erreur"
	    exit 2
	fi
    fi
done

if ! $test_mode; then
## les lignes ci dessous sont destinnées à mettre des 0 en debut des numéros de ligne et de colonnes
## Il y a certainement plus simple mais là c'est du rapide et efficace.
    rename 's/_(\d\d)_(\d+\.jpg)$/_0$1_$2/' ${prefix}_*
    rename 's/_(\d)_(\d+\.jpg)$/_00$1_$2/' ${prefix}_*
    rename 's/_(\d+)_(\d\d)(\.jpg)$/_$1_0$2$3/' ${prefix}_*
    rename 's/_(\d+)_(\d)(\.jpg)$/_$1_00$2$3/' ${prefix}_*
fi
