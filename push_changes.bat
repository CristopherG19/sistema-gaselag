@echo off
echo "=== SISTEMA GASELAG - PUSH CHANGES ==="
cd /d "c:\xampp\htdocs\sistema-gaselag"
echo "Directorio actual:"
pwd

echo "Estado del repositorio:"
git status

echo "Agregando cambios:"
git add -A

echo "Haciendo commit:"
git commit -m "✨ Sistema completo de edición de registros con validaciones y correcciones críticas"

echo "Verificando remoto:"
git remote -v

echo "Configurando remoto si es necesario:"
git remote set-url origin https://github.com/CristopherG19/sistema-gaselag.git

echo "Subiendo cambios:"
git push -u origin main

echo "Verificando resultado:"
git log --oneline -3

pause
