for i in `find . -maxdepth 1 -type d \( ! -iname ".*" \)`
do
	echo "transformando aplicacion $i ..."
	xpotronize $i -f
done
