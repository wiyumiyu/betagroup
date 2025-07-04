-- OJO: Eliminaci�n de datos de BETAGROUP desde ADMIN SYS
DROP USER betagroup CASCADE;
DROP TABLESPACE tbs_betagroup INCLUDING CONTENTS AND DATAFILES;

-- EJECUTAR COMO ADMIN_SYS
CREATE USER betagroup IDENTIFIED BY beta123;

GRANT CONNECT, RESOURCE TO betagroup;
GRANT UNLIMITED TABLESPACE TO betagroup;

-- Permisos para programaci�n PL/SQL
GRANT CREATE PROCEDURE TO betagroup;
GRANT CREATE TRIGGER TO betagroup;
GRANT CREATE SEQUENCE TO betagroup;
GRANT CREATE VIEW TO betagroup;

-- Permiso para usar funciones criptogr�ficas
GRANT EXECUTE ON DBMS_CRYPTO TO betagroup;

/*Crear un nuevo tablespace*/

CREATE TABLESPACE tbs_betagroup
    DATAFILE 'C:\Oracle\oradata\ORCL\tbs_betagroup.dbf' SIZE 50M
    AUTOEXTEND ON NEXT 10M;

ALTER USER betagroup
    DEFAULT TABLESPACE tbs_betagroup;

commit


-- OJO DESDE AQU� SE EMPIEZA A EJECUTAR DESDE EL ESQUEMA BETAGROUP

-- ------------------------------------------------- TABLAS ---------------------------------------------------------------------

/* Crear tabla inicial (USUARIOS) */
CREATE TABLE USUARIO (
  ID_USUARIO NUMBER PRIMARY KEY,
  NOMBRE_USUARIO VARCHAR2(100) NOT NULL,
  CONTRASENA VARCHAR2(100) NOT NULL,
  TELEFONO VARCHAR2(20),
  CORREO VARCHAR2(50) NOT NULL,
  ROL NUMBER,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ESTADO NUMBER(1) DEFAULT 1 -- 1 = habilitado, 0 = deshabilitado
);


-- Tabla de PROVEEDOR
CREATE TABLE PROVEEDOR (
  ID_PROVEEDOR NUMBER PRIMARY KEY,
  NOMBRE_PROVEEDOR VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(50) NOT NULL,
  DIRECCION_PROVEEDOR VARCHAR2(100) NOT NULL,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ESTADO NUMBER(1) DEFAULT 1 -- 1 = habilitado, 0 = deshabilitado
);

-- Crear tabla TIPO_CLINICA
CREATE TABLE TIPO_CLINICA (
  ID_TIPO_CLINICA NUMBER PRIMARY KEY,
  DESCRIPCION VARCHAR2(100) NOT NULL
);

-- Crear tabla CLIENTE (FK a TIPO_CLINICA)
CREATE TABLE CLIENTE (
  ID_CLIENTE NUMBER PRIMARY KEY,
  NOMBRE_CLIENTE VARCHAR2(100) NOT NULL,
  CORREO VARCHAR2(100) NOT NULL,
  ID_TIPO_CLINICA NUMBER,
  CONSTRAINT FK_CLIENTE_TIPO_CLINICA FOREIGN KEY (ID_TIPO_CLINICA)
    REFERENCES TIPO_CLINICA(ID_TIPO_CLINICA)
);

-- Tabla de TELEFONO_PROVEEDOR
CREATE TABLE TELEFONO_PROVEEDOR (
  ID_TELEFONO NUMBER PRIMARY KEY,
  TELEFONO VARCHAR2(20) NOT NULL,
  ID_PROVEEDOR NUMBER NOT NULL,
  CONSTRAINT FK_TEL_PROV FOREIGN KEY (ID_PROVEEDOR) REFERENCES PROVEEDOR(ID_PROVEEDOR)
);

-- Tabla de CATEGORIA
CREATE TABLE CATEGORIA (
  ID_CATEGORIA NUMBER PRIMARY KEY,
  NOMBRE_CATEGORIA VARCHAR2(100) NOT NULL
);

-- Tabla de PRODUCTO
CREATE TABLE PRODUCTO (
  ID_PRODUCTO NUMBER PRIMARY KEY,
  NOMBRE_PRODUCTO VARCHAR2(100) NOT NULL,
  PRECIO NUMBER NOT NULL,
  FECHA_REGISTRO DATE DEFAULT SYSDATE,
  ID_PROVEEDOR NUMBER NOT NULL,
  ID_CATEGORIA NUMBER NOT NULL,
  CONSTRAINT FK_PROD_PROV FOREIGN KEY (ID_PROVEEDOR) REFERENCES PROVEEDOR(ID_PROVEEDOR),
  CONSTRAINT FK_PROD_CAT FOREIGN KEY (ID_CATEGORIA) REFERENCES CATEGORIA(ID_CATEGORIA)
);

-- Tabla de VENTAS
CREATE TABLE VENTA (
  ID_VENTA NUMBER PRIMARY KEY,
  NUMERO NUMBER DEFAULT 0, 
  FECHA DATE DEFAULT SYSDATE,
  IMPUESTOS NUMBER DEFAULT 0,
  ID_CLIENTE NUMBER NOT NULL,
  ID_USUARIO NUMBER NOT NULL,
   ESTADO NUMBER(1) DEFAULT 1 ,
  CONSTRAINT FK_VENT_CLIE FOREIGN KEY (ID_CLIENTE) REFERENCES CLIENTE(ID_CLIENTE),
  CONSTRAINT FK_VENT_USUA FOREIGN KEY (ID_USUARIO) REFERENCES USUARIO(ID_USUARIO)
);

-- Tabla de VENTA_DETALLES
CREATE TABLE VENTA_DETALLE (
  ID_VENTA_DETALLE NUMBER PRIMARY KEY,
  CANTIDAD NUMBER DEFAULT 0,
  PRECIO_UNITARIO NUMBER DEFAULT 0,
  DESCUENTO NUMBER DEFAULT 0,
  ID_PRODUCTO NUMBER NOT NULL,
  ID_VENTA NUMBER NOT NULL,
  CONSTRAINT FK_VEDE_PROD FOREIGN KEY (ID_PRODUCTO) REFERENCES PRODUCTO(ID_PRODUCTO),
  CONSTRAINT FK_VEDE_VENT FOREIGN KEY (ID_VENTA) REFERENCES VENTA(ID_VENTA)
  
);


-- ------------------------------------------------- PROCEDIMIENTOS ALMACENADOS ---------------------------------------------------------------------

-- 1. Funcion para encriptar la contrase�a usando SHA-256
CREATE OR REPLACE FUNCTION HASH_PASSWORD(p_pass VARCHAR2) RETURN VARCHAR2 IS
BEGIN
  -- Convierte la contrase�a a un hash SHA-256 y lo devuelve en hexadecimal
  RETURN RAWTOHEX(
    DBMS_CRYPTO.HASH(
      UTL_I18N.STRING_TO_RAW(p_pass, 'AL32UTF8'), -- convierte el texto en bytes
      DBMS_CRYPTO.HASH_SH256                      -- aplica el algoritmo SHA-256
    )
  );
END;
/

-- 2. Procedimiento para validar si el login es correcto
CREATE OR REPLACE PROCEDURE VALIDAR_LOGIN (
    p_correo       IN  VARCHAR2,
    p_pass         IN  VARCHAR2,
    p_resultado    OUT NUMBER,
    p_id_usuario   OUT NUMBER,
    p_nombre       OUT VARCHAR2,
    p_rol          OUT VARCHAR2
) IS
    v_hash VARCHAR2(64);
BEGIN
    -- Hash de la contrase�a ingresada
    v_hash := HASH_PASSWORD(p_pass);

    -- Validar credenciales y estado
    SELECT id_usuario, nombre_usuario, rol
    INTO p_id_usuario, p_nombre, p_rol
    FROM USUARIO
    WHERE correo = p_correo
      AND contrasena = v_hash
      AND estado = 1;  -- Solo usuarios habilitados

    p_resultado := 1; -- login v�lido

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_resultado := 0; -- login inv�lido o usuario deshabilitado
END;
/

-- 3. Procedimiento que devuelve todos los usuarios usando un cursor
CREATE OR REPLACE PROCEDURE LISTAR_USUARIOS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_USUARIO, NOMBRE_USUARIO, TELEFONO, CORREO, ROL, FECHA_REGISTRO, ESTADO
        FROM USUARIO
        WHERE ESTADO = 1
        ORDER BY ID_USUARIO;
END;
/


-- 4. Procedimiento PL/SQL para crear autom�ticamente una secuencia y un trigger
-- que permiten autoincrementar el ID de cualquier tabla que indiquemos

CREATE OR REPLACE PROCEDURE CREAR_AUTOINCREMENTO (
    p_tabla       IN VARCHAR2,       -- nombre de la tabla (ej: 'USUARIO')
    p_campo_id    IN VARCHAR2        -- nombre del campo ID (ej: 'ID_USUARIO')
) AS
    v_seq_name    VARCHAR2(100);     -- nombre que se usar� para la secuencia
    v_trigger_name VARCHAR2(100);    -- nombre que se usar� para el trigger
BEGIN
    -- Construimos los nombres de la secuencia y del trigger en base al nombre de la tabla
    v_seq_name := 'SEQ_ID_' || UPPER(p_tabla);
    v_trigger_name := 'TRG_AUTOINC_' || UPPER(p_tabla);

    -- Intentamos eliminar la secuencia anterior (si ya exist�a), para evitar errores
    EXECUTE IMMEDIATE '
        BEGIN
            EXECUTE IMMEDIATE ''DROP SEQUENCE ' || v_seq_name || ''';
        EXCEPTION
            WHEN OTHERS THEN NULL; -- Si no existe, no pasa nada
        END;';

    -- Creamos la nueva secuencia desde 1, que se usar� para generar los IDs
    EXECUTE IMMEDIATE '
        CREATE SEQUENCE ' || v_seq_name || '
        START WITH 1
        INCREMENT BY 1
        NOCACHE
        NOCYCLE';

    -- Creamos el trigger que se ejecuta autom�ticamente antes de cada INSERT
    -- Si no se indica un ID, el trigger asigna uno usando la secuencia
    EXECUTE IMMEDIATE '
        CREATE OR REPLACE TRIGGER ' || v_trigger_name || '
        BEFORE INSERT ON ' || p_tabla || '
        FOR EACH ROW
        WHEN (NEW.' || p_campo_id || ' IS NULL)
        BEGIN
            SELECT ' || v_seq_name || '.NEXTVAL INTO :NEW.' || p_campo_id || ' FROM DUAL;
        END;';
END;
/

-- 5. Procedimiento que inserta un usuario

CREATE OR REPLACE PROCEDURE insertar_usuario (
    p_nombre     IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_contrasena IN VARCHAR2,
    p_telefono   IN USUARIO.TELEFONO%TYPE,
    p_correo     IN USUARIO.CORREO%TYPE,
    p_rol        IN USUARIO.ROL%TYPE
) AS
BEGIN
    INSERT INTO USUARIO (
        NOMBRE_USUARIO,
        CONTRASENA,
        TELEFONO,
        CORREO,
        ROL,
        ESTADO
    ) VALUES (
        p_nombre,
        HASH_PASSWORD(p_contrasena),
        p_telefono,
        p_correo,
        p_rol,
        1 -- Habilitado por defecto
    );
END;
/


-- 6. Procedimiento para actualizar usuario

CREATE OR REPLACE PROCEDURE actualizar_usuario (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_contrasena  IN VARCHAR2, -- Puede venir NULL si no se quiere actualizar
    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE,
    p_estado      IN USUARIO.ESTADO%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol,
        ESTADO         = p_estado,
        CONTRASENA     = CASE
                           WHEN p_contrasena IS NOT NULL THEN HASH_PASSWORD(p_contrasena)
                           ELSE CONTRASENA
                         END
    WHERE ID_USUARIO = p_id_usuario;
END;
/


-- 7. Procedimiento para actualizar usuario sin contrase�a
CREATE OR REPLACE PROCEDURE actualizar_usuario_sc (
    p_id_usuario  IN USUARIO.ID_USUARIO%TYPE,
    p_nombre      IN USUARIO.NOMBRE_USUARIO%TYPE,
    p_telefono    IN USUARIO.TELEFONO%TYPE,
    p_correo      IN USUARIO.CORREO%TYPE,
    p_rol         IN USUARIO.ROL%TYPE,
    p_estado      IN USUARIO.ESTADO%TYPE
) AS
BEGIN
    UPDATE USUARIO
    SET
        NOMBRE_USUARIO = p_nombre,
        TELEFONO       = p_telefono,
        CORREO         = p_correo,
        ROL            = p_rol,
        ESTADO         = p_estado
    WHERE ID_USUARIO = p_id_usuario;
END;
/

-- 8. Procedimiento para eliminar usuario

CREATE OR REPLACE PROCEDURE eliminar_usuario (
    p_id IN USUARIO.ID_USUARIO%TYPE
) AS
BEGIN
    DELETE FROM USUARIO
    WHERE ID_USUARIO = p_id;
END;
/

-- 9. Procedimiento para crear cliente

CREATE OR REPLACE PROCEDURE insertar_cliente (
    p_nombre_cliente  IN CLIENTE.NOMBRE_CLIENTE%TYPE,
    p_correo          IN CLIENTE.CORREO%TYPE,
    p_id_tipo_clinica IN CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    INSERT INTO CLIENTE (
        NOMBRE_CLIENTE,
        CORREO,
        ID_TIPO_CLINICA
    ) VALUES (
        p_nombre_cliente,
        p_correo,
        p_id_tipo_clinica
    );
END;
/

-- 10. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE actualizar_cliente (
    p_id_cliente    IN CLIENTE.ID_cliente%TYPE,
    p_nombre_cliente  IN CLIENTE.NOMBRE_CLIENTE%TYPE,
    p_correo          IN CLIENTE.CORREO%TYPE,
    p_id_tipo_clinica IN CLIENTE.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    UPDATE CLIENTE
    SET
        NOMBRE_CLIENTE  = p_nombre_cliente,
        CORREO          = p_correo,
        ID_TIPO_CLINICA = p_id_tipo_clinica
    WHERE ID_CLIENTE = p_id_cliente;
END;
/

-- 11. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE eliminar_cliente (
    p_id_cliente IN CLIENTE.ID_CLIENTE%TYPE
) AS
BEGIN
    DELETE FROM CLIENTE
    WHERE ID_CLIENTE = p_id_cliente;
END;
/

-- 12. Procedimiento para actualizar cliente

CREATE OR REPLACE PROCEDURE listar_clientes (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT c.ID_CLIENTE,
               c.NOMBRE_CLIENTE,
               c.CORREO,
               tc.DESCRIPCION AS TIPO_CLINICA
        FROM CLIENTE c
        LEFT JOIN TIPO_CLINICA tc ON c.ID_TIPO_CLINICA = tc.ID_TIPO_CLINICA
        ORDER BY c.ID_CLIENTE;
END;
/

-- 13. Procedimiento para insertar una clinica nueva

CREATE OR REPLACE PROCEDURE insertar_tipo_clinica (
    p_descripcion IN TIPO_CLINICA.DESCRIPCION%TYPE
) AS
BEGIN
    INSERT INTO TIPO_CLINICA (DESCRIPCION)
    VALUES (p_descripcion);
END;
/

-- 14. Procedimiento para actualizar una clinica

CREATE OR REPLACE PROCEDURE actualizar_tipo_clinica (
    p_id_tipo_clinica IN TIPO_CLINICA.ID_TIPO_CLINICA%TYPE,
    p_descripcion     IN TIPO_CLINICA.DESCRIPCION%TYPE
) AS
BEGIN
    UPDATE TIPO_CLINICA
    SET DESCRIPCION = p_descripcion
    WHERE ID_TIPO_CLINICA = p_id_tipo_clinica;
END;
/

-- 15. Procedimiento para eliminar una clinica

CREATE OR REPLACE PROCEDURE eliminar_tipo_clinica (
    p_id_tipo_clinica IN TIPO_CLINICA.ID_TIPO_CLINICA%TYPE
) AS
BEGIN
    DELETE FROM TIPO_CLINICA
    WHERE ID_TIPO_CLINICA = p_id_tipo_clinica;
END;
/

-- 16. Procedimiento para listar las clinicas

CREATE OR REPLACE PROCEDURE listar_tipos_clinica (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TIPO_CLINICA, DESCRIPCION
        FROM TIPO_CLINICA
        ORDER BY ID_TIPO_CLINICA;
END;
/

-- 17. Procedimiento que devuelve todos los productos usando un cursor
CREATE OR REPLACE PROCEDURE LISTAR_PRODUCTOS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            P.ID_PRODUCTO,
            P.NOMBRE_PRODUCTO,
            P.PRECIO,
            P.FECHA_REGISTRO,
            PR.NOMBRE_PROVEEDOR,
            C.NOMBRE_CATEGORIA
        FROM PRODUCTO P
        JOIN PROVEEDOR PR ON P.ID_PROVEEDOR = PR.ID_PROVEEDOR
        JOIN CATEGORIA C ON P.ID_CATEGORIA = C.ID_CATEGORIA
        ORDER BY P.ID_PRODUCTO;
END;
/

-- 18. Procedimiento que inserta un producto

CREATE OR REPLACE PROCEDURE insertar_producto (
    p_nombre_producto   IN PRODUCTO.NOMBRE_PRODUCTO%TYPE,
    p_precio            IN PRODUCTO.PRECIO%TYPE,
    p_id_proveedor      IN PRODUCTO.ID_PROVEEDOR%TYPE,
    p_id_categoria      IN PRODUCTO.ID_CATEGORIA%TYPE
) AS
BEGIN
    INSERT INTO PRODUCTO (
        NOMBRE_PRODUCTO,
        PRECIO,
        ID_PROVEEDOR,
        ID_CATEGORIA
    ) VALUES (
        p_nombre_producto,
        p_precio,
        p_id_proveedor,
        p_id_categoria
    );
END;
/


-- 19. Procedimiento para actualizar un producto

CREATE OR REPLACE PROCEDURE actualizar_producto (
    p_id_producto       IN PRODUCTO.ID_PRODUCTO%TYPE,
    p_nombre_producto   IN PRODUCTO.NOMBRE_PRODUCTO%TYPE,
    p_precio            IN PRODUCTO.PRECIO%TYPE,
    p_id_proveedor      IN PRODUCTO.ID_PROVEEDOR%TYPE,
    p_id_categoria      IN PRODUCTO.ID_CATEGORIA%TYPE
) AS
BEGIN
    UPDATE PRODUCTO
    SET
        NOMBRE_PRODUCTO = p_nombre_producto,
        PRECIO = p_precio,
        ID_PROVEEDOR = p_id_proveedor,
        ID_CATEGORIA = p_id_categoria
    WHERE ID_PRODUCTO = p_id_producto;
END;
/

-- 20. Procedimiento para eliminar un producto

CREATE OR REPLACE PROCEDURE eliminar_producto (
    p_id IN PRODUCTO.ID_PRODUCTO%TYPE
) AS
BEGIN
    DELETE FROM PRODUCTO
    WHERE ID_PRODUCTO = p_id;
END;
/

-- 21. Procedimiento que devuelve todas las categorias 

CREATE OR REPLACE PROCEDURE LISTAR_CATEGORIAS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            C.ID_CATEGORIA,
            C.NOMBRE_CATEGORIA
        FROM CATEGORIA C
        ORDER BY C.ID_CATEGORIA;
END;
/

-- 22. Procedimiento que inserta una categoria

CREATE OR REPLACE PROCEDURE insertar_categoria (
    p_nombre_categoria IN CATEGORIA.NOMBRE_CATEGORIA%TYPE
) AS
BEGIN
    INSERT INTO CATEGORIA (
        NOMBRE_CATEGORIA
    ) VALUES (
        p_nombre_categoria
    );
END;
/

-- 23. Procedimiento para eliminar una categoria

CREATE OR REPLACE PROCEDURE eliminar_categoria (
    p_id IN CATEGORIA.ID_CATEGORIA%TYPE
) AS
BEGIN
    DELETE FROM CATEGORIA
    WHERE ID_CATEGORIA = p_id;
END;
/

-- 24. Procedimiento que devuelve todos los proveedores

CREATE OR REPLACE PROCEDURE LISTAR_PROVEEDORES(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
  OPEN p_cursor FOR
    SELECT 
      p.ID_PROVEEDOR,
      p.NOMBRE_PROVEEDOR,
      p.CORREO,
      p.DIRECCION_PROVEEDOR,
      p.FECHA_REGISTRO,
      LISTAGG(t.TELEFONO, CHR(10)) WITHIN GROUP (ORDER BY t.TELEFONO) AS TELEFONOS
    FROM 
      PROVEEDOR p
    LEFT JOIN 
      TELEFONO_PROVEEDOR t ON p.ID_PROVEEDOR = t.ID_PROVEEDOR
    WHERE 
      p.ESTADO = 1
    GROUP BY 
      p.ID_PROVEEDOR, p.NOMBRE_PROVEEDOR, p.CORREO, p.DIRECCION_PROVEEDOR, p.FECHA_REGISTRO
    ORDER BY 
      p.ID_PROVEEDOR;
END;
/

-- 25. Procedimiento para insertar_proveedor --

CREATE OR REPLACE PROCEDURE insertar_proveedor (
    p_nombre    IN proveedor.nombre_proveedor%TYPE,
    p_correo    IN proveedor.correo%TYPE,
    p_direccion IN proveedor.direccion_proveedor%TYPE,
    p_estado    IN proveedor.estado%TYPE DEFAULT 1,
    p_id_out    OUT proveedor.id_proveedor%TYPE
) AS
BEGIN
    INSERT INTO proveedor (
        nombre_proveedor,
        correo,
        direccion_proveedor,
        estado
    ) VALUES (
        p_nombre,
        p_correo,
        p_direccion,
        p_estado
    )
    RETURNING id_proveedor INTO p_id_out;
END;
/
   
-- 26. procedimiento para actualizar proveedor

CREATE OR REPLACE PROCEDURE actualizar_proveedor (
    p_id_proveedor IN proveedor.id_proveedor%TYPE,
    p_nombre       IN proveedor.nombre_proveedor%TYPE,
    p_correo       IN proveedor.correo%TYPE,
    p_direccion    IN proveedor.direccion_proveedor%TYPE,
    p_estado       IN proveedor.estado%TYPE
) AS
BEGIN
    UPDATE proveedor
    SET
        nombre_proveedor = p_nombre,
        correo = p_correo,
        direccion_proveedor = p_direccion,
        estado = p_estado
    WHERE
        id_proveedor = p_id_proveedor;
END;
/

-- 27. PROCEDIMIENTO PARA ELIMINAR PROVEEDOR
CREATE OR REPLACE PROCEDURE eliminar_proveedor (
    p_id_proveedor IN proveedor.id_proveedor%TYPE
) AS
BEGIN
    DELETE FROM proveedor
    WHERE id_proveedor = p_id_proveedor;
END;
/

-- 28. PROCEDIMIENTO PARA HABILITAR UN PROVEEDOR CAMBIANDO SU ESTADO A 1
CREATE OR REPLACE PROCEDURE habilitar_proveedor (
    p_id IN PROVEEDOR.ID_PROVEEDOR%TYPE
) AS
BEGIN
    UPDATE PROVEEDOR
    SET ESTADO = 1
    WHERE ID_PROVEEDOR = p_id;
END;
/

-- 29. PROCEDIMIENTO PARA OBTENER DATOS DE UN PROVEEDOR ESPEC�FICO
CREATE OR REPLACE PROCEDURE OBTENER_PROVEEDOR (
    p_id       IN  NUMBER,
    p_cursor   OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO
        FROM PROVEEDOR
        WHERE ID_PROVEEDOR = p_id;
END;
/

-- 30. PROCEDIMIENTO PARA OBTENER SOLO LOS ID DE TEL�FONOS DE UN PROVEEDOR
CREATE OR REPLACE PROCEDURE OBTENER_ID_TELEFONOS (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_cursor       OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TELEFONO
        FROM TELEFONO_PROVEEDOR
        WHERE ID_PROVEEDOR = p_id_proveedor;
END;
/

-- 31. PROCEDIMIENTO PARA OBTENER LOS TEL�FONOS COMPLETOS DE UN PROVEEDOR
CREATE OR REPLACE PROCEDURE OBTENER_TELEFONOS_PROVEEDOR (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_cursor       OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_TELEFONO, TELEFONO
        FROM TELEFONO_PROVEEDOR
        WHERE ID_PROVEEDOR = p_id_proveedor;
END;
/

-- 32. PROCEDIMIENTO PARA ACTUALIZAR UN TEL�FONO EXISTENTE
CREATE OR REPLACE PROCEDURE actualizar_telefono_proveedor (
    p_id_telefono   IN TELEFONO_PROVEEDOR.ID_TELEFONO%TYPE,
    p_telefono      IN TELEFONO_PROVEEDOR.TELEFONO%TYPE
) AS
BEGIN
    UPDATE TELEFONO_PROVEEDOR
    SET TELEFONO = p_telefono
    WHERE ID_TELEFONO = p_id_telefono;
END;
/

-- 33. PROCEDIMIENTO PARA ELIMINAR UN TEL�FONO
CREATE OR REPLACE PROCEDURE eliminar_telefono (
    p_id_tel IN TELEFONO_PROVEEDOR.ID_TELEFONO%TYPE
)
AS
BEGIN
    DELETE FROM TELEFONO_PROVEEDOR
    WHERE ID_TELEFONO = p_id_tel;
END;
/

-- 34. PROCEDIMIENTO PARA INSERTAR UN NUEVO TEL�FONO PARA UN PROVEEDOR
CREATE OR REPLACE PROCEDURE insertar_telefono_proveedor (
    p_id_proveedor IN TELEFONO_PROVEEDOR.ID_PROVEEDOR%TYPE,
    p_telefono     IN TELEFONO_PROVEEDOR.TELEFONO%TYPE
) AS
BEGIN
    INSERT INTO TELEFONO_PROVEEDOR (ID_PROVEEDOR, TELEFONO)
    VALUES (p_id_proveedor, p_telefono);
END;
/

  
-- 35. PROCEDIMIENTO PARA ELIMINAR UNA VENTA
CREATE OR REPLACE PROCEDURE eliminar_venta (
    p_id IN VENTA.ID_VENTA%TYPE
) AS
BEGIN
    DELETE FROM VENTA
    WHERE ID_VENTA = p_id;
END;
/

-- 36. PROCEDIMIENTO PARA ELIMINAR UNA VENTA_DETALLE
CREATE OR REPLACE PROCEDURE eliminar_venta_detalle (
    p_id IN VENTA_DETALLE.ID_VENTA_DETALLE%TYPE
) AS
BEGIN
    DELETE FROM VENTA_DETALLE
    WHERE ID_VENTA_DETALLE = p_id;
END;
/

-- 37. PROCEDIMIENTO QUE DEVUELVE TODAS LAS VENTAS USANDO UN CURSOR
CREATE OR REPLACE PROCEDURE LISTAR_VENTAS(p_cursor OUT SYS_REFCURSOR) AS
BEGIN
    -- Abrimos el cursor con los datos de todas las ventas
    OPEN p_cursor FOR
        SELECT VENTA.ID_VENTA, VENTA.NUMERO, VENTA.FECHA, VENTA.IMPUESTOS, CLIENTE.NOMBRE_CLIENTE, USUARIO.NOMBRE_USUARIO
        FROM VENTA
        LEFT JOIN CLIENTE ON CLIENTE.ID_CLIENTE = VENTA.ID_CLIENTE
        LEFT JOIN USUARIO ON USUARIO.ID_USUARIO = VENTA.ID_USUARIO
        ORDER BY VENTA.NUMERO;
END;
/

-- 38. PROCEDIMIENTO PARA INSERTAR UNA VENTA
CREATE OR REPLACE PROCEDURE insertar_venta (
    p_numero     IN VENTA.NUMERO%TYPE,
    p_impuestos  IN VENTA.IMPUESTOS%TYPE,
    p_id_cliente IN VENTA.ID_CLIENTE%TYPE,
    p_id_usuario IN VENTA.ID_USUARIO%TYPE
) AS
BEGIN
    INSERT INTO VENTA (
        NUMERO,
        IMPUESTOS,
        ID_CLIENTE,
        ID_USUARIO
    ) VALUES (
        p_numero,
        p_impuestos,
        p_id_cliente,
        p_id_usuario
    );
END;
/

-- 39. PROCEDIMIENTO PARA INSERTAR UN DETALLE DE VENTA
CREATE OR REPLACE PROCEDURE insertar_venta_detalle (
    p_cantidad       IN VENTA_DETALLE.CANTIDAD%TYPE,
    p_precio_unitario IN VENTA_DETALLE.PRECIO_UNITARIO%TYPE,
    p_descuento      IN VENTA_DETALLE.DESCUENTO%TYPE,
    p_id_producto    IN VENTA_DETALLE.ID_PRODUCTO%TYPE,
    p_id_venta       IN VENTA_DETALLE.ID_VENTA%TYPE
) AS
BEGIN
    INSERT INTO VENTA_DETALLE (
        CANTIDAD,
        PRECIO_UNITARIO,
        DESCUENTO,
        ID_PRODUCTO,
        ID_VENTA
    ) VALUES (
        p_cantidad,
        p_precio_unitario,
        p_descuento,
        p_id_producto,
        p_id_venta
    );
END;
/

-- 40. PROCEDIMIENTO QUE DEVUELVE EL N�MERO MAYOR DE LAS VENTAS
CREATE OR REPLACE PROCEDURE OBTENER_MAX_NUMERO_VENTA (
    p_max_numero OUT VENTA.NUMERO%TYPE
) AS
BEGIN
    SELECT NVL(MAX(NUMERO), 0)
    INTO p_max_numero
    FROM VENTA;
END;
/

-- 41. Procedimiento que devuelve una venta a partir de un id
CREATE OR REPLACE PROCEDURE OBTENER_VENTA (
    p_id_venta   IN  VENTA.ID_VENTA%TYPE,
    p_numero     OUT VENTA.NUMERO%TYPE,
    p_impuestos  OUT VENTA.IMPUESTOS%TYPE,
    p_id_cliente OUT VENTA.ID_CLIENTE%TYPE
) AS
BEGIN
    SELECT NUMERO, IMPUESTOS, ID_CLIENTE
    INTO   p_numero, p_impuestos, p_id_cliente
    FROM   VENTA
    WHERE  ID_VENTA = p_id_venta;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_numero     := NULL;
        p_impuestos  := NULL;
        p_id_cliente := NULL;
    WHEN OTHERS THEN
        RAISE;
END;
/

--42. procedimiento que devuelve el detalle de una venta
CREATE OR REPLACE PROCEDURE LISTAR_DETALLES_VENTA (
    p_id_venta IN VENTA_DETALLE.ID_VENTA%TYPE,
    p_cursor OUT SYS_REFCURSOR
)
AS
BEGIN
    OPEN p_cursor FOR
        SELECT 
            vd.ID_PRODUCTO,
            p.NOMBRE_PRODUCTO,
            vd.CANTIDAD,
            vd.DESCUENTO,
            vd.PRECIO_UNITARIO
        FROM VENTA_DETALLE vd
        JOIN PRODUCTO p ON vd.ID_PRODUCTO = p.ID_PRODUCTO
        WHERE vd.ID_VENTA = p_id_venta;
END;
/

--42. procedimiento que devuelve la ultima venta ingresada
CREATE OR REPLACE PROCEDURE OBTENER_ULTIMO_ID_VENTA (
    p_id_venta OUT NUMBER
) AS
BEGIN
    SELECT SEQ_ID_VENTA.CURRVAL INTO p_id_venta FROM DUAL;
END;
/


--43. procedimiento que optiene un producto a partir de un id
CREATE OR REPLACE PROCEDURE OBTENER_PRODUCTO (
    p_id_producto IN PRODUCTO.ID_PRODUCTO%TYPE,
    p_cursor      OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA
        FROM PRODUCTO
        WHERE ID_PRODUCTO = p_id_producto;
END;
/


-- -------------------------- VISTAS ------------------------------------------------------

-- 1. Vista de Usuarios Deshabilitados
CREATE OR REPLACE VIEW V_USUARIOS_DESHABILITADOS AS
SELECT 
    ID_USUARIO,
    NOMBRE_USUARIO,
    TELEFONO,
    CORREO,
    ROL,
    FECHA_REGISTRO
FROM 
    USUARIO
WHERE 
    ESTADO = 0;

-- 2. Vista de clientes filtrados por Tipo_Clinica
CREATE OR REPLACE VIEW V_CLIENTES_CON_TIPO_CLINICA AS
SELECT
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE,
    C.CORREO,
    C.ID_TIPO_CLINICA,
    TC.DESCRIPCION AS TIPO_CLINICA
FROM
    CLIENTE C
JOIN
    TIPO_CLINICA TC ON C.ID_TIPO_CLINICA = TC.ID_TIPO_CLINICA;

-- 3. Vista de cantidad de compras efectuadas por cliente
CREATE OR REPLACE VIEW V_CANTIDAD_VENTAS_POR_CLIENTE AS
SELECT
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE,
    COUNT(V.ID_VENTA) AS CANTIDAD_VENTAS
FROM
    CLIENTE C
JOIN
    VENTA V ON C.ID_CLIENTE = V.ID_CLIENTE
GROUP BY
    C.ID_CLIENTE,
    C.NOMBRE_CLIENTE
HAVING
    COUNT(V.ID_VENTA) >= 1
ORDER BY
    C.NOMBRE_CLIENTE;

-- 4. Vista de Proveedores Deshabilitados
CREATE OR REPLACE VIEW V_PROVEEDORES_DESHABILITADOS AS
SELECT 
    p.ID_PROVEEDOR,
    p.NOMBRE_PROVEEDOR,
    p.CORREO,
    p.DIRECCION_PROVEEDOR,
    -- Agrupar tel�fonos en una sola columna
    (
        SELECT LISTAGG(t.TELEFONO, CHR(10)) WITHIN GROUP (ORDER BY t.ID_TELEFONO)
        FROM TELEFONO_PROVEEDOR t
        WHERE t.ID_PROVEEDOR = p.ID_PROVEEDOR
    ) AS TELEFONOS
FROM 
    PROVEEDOR p
WHERE 
    p.ESTADO = 0;

-- Procedimiento que manpula la vista de Proveedores Deshabilitados 

CREATE OR REPLACE PROCEDURE LISTAR_PROVEEDORES_DESHABILITADOS (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT ID_PROVEEDOR, NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, TELEFONOS
        FROM V_PROVEEDORES_DESHABILITADOS;
END;
/

-- 5. VISTA PARA LOS 5 PRODUCTOS MENOS VENDIDOS
CREATE OR REPLACE VIEW VISTA_PRODUCTOS_MENOS_VENDIDOS AS
SELECT 
    p.ID_PRODUCTO,
    p.NOMBRE_PRODUCTO,
    c.NOMBRE_CATEGORIA,
    NVL(SUM(vd.CANTIDAD), 0) AS TOTAL_VENDIDO
FROM PRODUCTO p
LEFT JOIN VENTA_DETALLE vd ON p.ID_PRODUCTO = vd.ID_PRODUCTO
JOIN CATEGORIA c ON p.ID_CATEGORIA = c.ID_CATEGORIA
GROUP BY p.ID_PRODUCTO, p.NOMBRE_PRODUCTO, c.NOMBRE_CATEGORIA
ORDER BY TOTAL_VENDIDO ASC
FETCH FIRST 5 ROWS ONLY;

-- Procedimiento vara consultar lo productos menos vendidos
CREATE OR REPLACE PROCEDURE LISTAR_PRODUCTOS_MENOS_VENDIDOS (
    p_cursor OUT SYS_REFCURSOR
) AS
BEGIN
    OPEN p_cursor FOR
        SELECT * FROM VISTA_PRODUCTOS_MENOS_VENDIDOS;
END;
/



-- -------------------------- TRIGGER ------------------------------------------------------

-- 1. INSERTAR +506 al n�mero

CREATE OR REPLACE TRIGGER TRG_TELEFONO_FORMATO
BEFORE INSERT ON TELEFONO_PROVEEDOR
FOR EACH ROW
BEGIN
  IF SUBSTR(:NEW.TELEFONO, 1, 4) != '+506' THEN
    :NEW.TELEFONO := '+506 ' || :NEW.TELEFONO;
  END IF;
END;
/

-- -------------------------- DATOS Y PRUEBAS ------------------------------------------------------

-- Llamamos al procedimiento para crear la secuencia y trigger para la tabla USUARIO
BEGIN
    CREAR_AUTOINCREMENTO('USUARIO', 'ID_USUARIO');
    CREAR_AUTOINCREMENTO('PRODUCTO', 'ID_PRODUCTO');
    CREAR_AUTOINCREMENTO('CATEGORIA', 'ID_CATEGORIA');
    CREAR_AUTOINCREMENTO('PROVEEDOR', 'ID_PROVEEDOR');
    CREAR_AUTOINCREMENTO('TELEFONO_PROVEEDOR', 'ID_TELEFONO');
    CREAR_AUTOINCREMENTO('CLIENTE', 'ID_CLIENTE');
    CREAR_AUTOINCREMENTO('TIPO_CLINICA', 'ID_TIPO_CLINICA');
    CREAR_AUTOINCREMENTO('VENTA', 'ID_VENTA');
    CREAR_AUTOINCREMENTO('VENTA_DETALLE', 'ID_VENTA_DETALLE');
END;
/

-- Insertamos datos en la tabla USUARIO
-- No se especifica ID_USUARIO porque se genera autom�ticamente por el trigger
-- La contrase�a se guarda encriptada con HASH_PASSWORD

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO) 
VALUES ('admin', HASH_PASSWORD('a'), '89802238', 'admin@gmail.com', 1, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('Vendedor 1', HASH_PASSWORD('a'), '89802238', 'vendedor@gmail.com', 0, 1);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO)
VALUES ('Vendedor 2', HASH_PASSWORD('a'), '89802238', 'vendedor2@gmail.com', 0, 0);

INSERT INTO USUARIO (NOMBRE_USUARIO, CONTRASENA, TELEFONO, CORREO, ROL, ESTADO) 
VALUES ('admin 2', HASH_PASSWORD('a'), '89802238', 'admin2@gmail.com', 1, 0);

-- Mostramos los usuarios insertados (veremos el hash, no la contrase�a original)
SELECT NOMBRE_USUARIO, CONTRASENA FROM USUARIO;

--Insertar una clinica
INSERT INTO TIPO_CLINICA (DESCRIPCION)
VALUES ('Cl�nica General');

--Insertar un cliente
INSERT INTO CLIENTE (NOMBRE_CLIENTE, CORREO, ID_TIPO_CLINICA)
VALUES ('Mar�a Jim�nez', 'maria.jimenez@gmail.com', 1);


-- 1. Insertar proveedores correctamente (incluye direcci�n)
INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('BeautyTech S.A.', 'ventas@beautytech.com', 'San Jos�, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Dermalaser CR', 'info@dermalaser.cr', 'Escaz�, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('DermaSkin', 'contacto@dermaskin.com', 'Santa Ana, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Est�tica Pura Vida', 'contacto@esteticapuravida.cr', 'Heredia, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('MedEquipos CR', 'ventas@medequiposcr.com', 'Alajuela, Costa Rica', 0);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('L�serClinic S.A.', 'info@laserclinic.cr', 'Cartago, Costa Rica', 1);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('Tecnobeauty', 'servicio@tecnobeauty.com', 'San Pedro, Costa Rica', 0);

INSERT INTO PROVEEDOR (NOMBRE_PROVEEDOR, CORREO, DIRECCION_PROVEEDOR, ESTADO)
VALUES ('CosmoEsthetics', 'soporte@cosmoesthetics.com', 'Liberia, Costa Rica', 1);

-- 2. Insertar tel�fonos (ya existen los proveedores)
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('89842738', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88883344', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88888784', 1);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('72119988', 2);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88991122', 3);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88776655', 4);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('88882211', 5);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('89997744', 6);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('87778899', 7);
INSERT INTO TELEFONO_PROVEEDOR (TELEFONO, ID_PROVEEDOR)
VALUES ('85001133', 8);


-- 3. Insertar categor�a
INSERT INTO CATEGORIA (NOMBRE_CATEGORIA)
VALUES ('Equipos Est�ticos');

-- 4. Insertar productos (ya existen proveedor y categor�a)
INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Soprano Titanium (Depilaci�n L�ser)', 500000, 1, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Multifuncional 6 en 1 con Lipol�ser 850mz y EMS', 1200000, 2, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Lampara de cirugia', 800000, 3, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('Laser CO2', 1500000, 2, 1);

INSERT INTO PRODUCTO (NOMBRE_PRODUCTO, PRECIO, ID_PROVEEDOR, ID_CATEGORIA)
VALUES ('L�ser de 4 longitudes soprano ', 1000000, 2, 1);

/*
SELECT *
FROM PRODUCTO;

SELECT *
FROM TELEFONO_PROVEEDOR;
*/
-- Ver procedimiento productos
VARIABLE rc REFCURSOR;
EXEC LISTAR_PRODUCTOS(:rc);
PRINT rc;

INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (1001, SYSDATE, 13, 1, 1);

INSERT INTO VENTA (NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES (1002, SYSDATE, 20, 1, 1);

INSERT INTO VENTA ( NUMERO, FECHA, IMPUESTOS, ID_CLIENTE, ID_USUARIO)
VALUES ( 1003, SYSDATE, 10, 1, 2);


commit;



