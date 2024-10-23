# master-fs

Pasos para subir el proyecto a GitHub
Crear un nuevo repositorio en GitHub:

Ve a GitHub y accede a tu cuenta.
Haz clic en el botón "New" o "Nuevo" para crear un nuevo repositorio.
Asigna un nombre a tu repositorio (por ejemplo, master-fs).
Selecciona si deseas que el repositorio sea público o privado.
Haz clic en "Create repository" (Crear repositorio).
Inicializar el repositorio local (si no lo has hecho):

Abre la terminal y navega a la carpeta de tu proyecto.
Si aún no has inicializado Git, ejecuta:
bash
Copiar código
git init
Agregar los archivos al repositorio:

Asegúrate de estar en la carpeta raíz de tu proyecto (donde está el archivo README.md).
Agrega todos los archivos:
bash
Copiar código
git add .
Hacer un commit:

Realiza un commit con un mensaje descriptivo:
bash
Copiar código
git commit -m "Primer commit: subiendo el proyecto inicial"
Configurar el repositorio remoto:

Establece la URL del repositorio remoto (reemplaza <URL_DEL_REPOSITORIO> con la URL que obtuviste de GitHub):
bash
Copiar código
git remote add origin <URL_DEL_REPOSITORIO>
Subir a la rama principal (main):

Sube tus cambios a la rama main sin necesidad de hacer pulls:
bash
Copiar código
git push -u origin main
Verificar en GitHub:

Visita tu repositorio en GitHub para asegurarte de que todos los archivos se han subido correctamente.
Notas adicionales
Si el repositorio ya tiene contenido y quieres sobrescribirlo, puedes usar:
bash
Copiar código
git push -f origin main
Pero ten cuidado, ya que esto puede eliminar datos del repositorio remoto.
