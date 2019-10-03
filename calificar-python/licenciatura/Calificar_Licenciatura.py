from string import *
from math import *
import sys
import xlwt

def califica(nombre, salida):

	i=0
	j=0
	# nombre=raw_input("Ingresa el nombre del archivo de resultados de los alumnos: ")
	
	diccionario={}
	diccionario_fichas={}
	diccionario_examenes={}
	diccionario_respuestas={}
	diccionario_respuestas_alumnos={}
	calificaciones={}
	tipos_examen = []

	alumnos="Elem_Evaluacion/"+nombre+".txt"
	diccionario=obtener_datos_alumnos(alumnos) #se obtiene un diccionario de esta forma {0: ['2', 'B', '9', '9', '9', '9', '9', '9','A','B',...,'D']}
	diccionario_respuestas=obtener_datos_respuestas("Elem_Evaluacion/Respuestas.txt") #se obtiene un diccionario de esta forma {'2B': ['A', 'B',..., 'D',]}
	rang_masc = obtener_rangos_archivo('Rangos/rangos_mascara.txt')

	for elemento in diccionario: #se obtiene un diccionario de las fichas de la siguiente forma {0: ['9', '9', '9', '9', '9', '9']}
		diccionario_fichas[i]=[]
		for numero in xrange(rang_masc['ficha'][0],rang_masc['ficha'][1]):
			diccionario_fichas[i].append(diccionario[elemento][numero]) 
		i+=1
	i=0

	for elemento in diccionario_fichas: #se concatenan los elementos del diccionario anterior para que queden de la siguiente forma {0: 999999}
		diccionario_fichas[elemento] = int(''.join(map(str,diccionario_fichas[elemento])))
	
	for elemento in diccionario: #se obtiene un diccionario del tipo de examen que tiene cada aspirante  {999999: ['2', 'B']}
		diccionario_examenes[diccionario_fichas[i]]=[]
		for numero in xrange(rang_masc['tipo_exa'][0],rang_masc['tipo_exa'][1]):
			diccionario_examenes[diccionario_fichas[i]].append(diccionario[elemento][numero])
		i+=1
	i=0
	

	for elemento in diccionario_examenes: #se concatenan los elementos del diccionario anterior para que queden de la siguiente forma {999999: '2B'}
		diccionario_examenes[elemento] = ''.join(diccionario_examenes[elemento])

	for elemento in diccionario: #se obtiene un diccionario de las respuestas que tiene cada aspirante  {999999: ['A', 'B',...'D']}
		diccionario_respuestas_alumnos[diccionario_fichas[i]]=[]
		for numero in xrange(rang_masc['respuestas'][0],rang_masc['respuestas'][1]): # valores correctos 8 128
			if (len(diccionario[elemento])<rang_masc['respuestas'][1]): # valor correcto 128
				diccionario[elemento].append("X")
			diccionario_respuestas_alumnos[diccionario_fichas[i]].append(diccionario[elemento][numero])
			if (diccionario_respuestas_alumnos[diccionario_fichas[i]][j]==" "):
				diccionario_respuestas_alumnos[diccionario_fichas[i]][j]="X"
			j+=1
		j=0
		print i+1
		if (len(diccionario_respuestas_alumnos[diccionario_fichas[i]])<len(diccionario_respuestas[diccionario_examenes[diccionario_fichas[i]]])):
			diccionario_respuestas_alumnos[diccionario_fichas[i]].append("X")
		i+=1
	i=0
	j=0
	
	resp_elim=obtener_respuestas_elim()
	retira_eliminadas(diccionario_respuestas,resp_elim)
	calificaciones=compara_respuestas(diccionario_examenes,diccionario_respuestas_alumnos,diccionario_respuestas,resp_elim)
	for elemento in diccionario_respuestas:
		tipos_examen.append(elemento)
	tipos_examen = sorted(tipos_examen)
	escribe_en_excel(calificaciones,nombre,tipos_examen,salida)

def obtener_datos_alumnos(nombre_archivo):
	archivo = open(nombre_archivo,"r")
	archivo.seek(0)
	
	datos={}
	i=0

	for linea in archivo.readlines():
		datos[i]=list(linea.rstrip('\n'))
		# print i+1
		i+=1
	return datos

def obtener_datos_respuestas(nombre_archivo):
	archivo = open(nombre_archivo,"r")
	archivo.seek(0)
	rangos_resp=obtener_rangos_archivo('Rangos/rangos_hoja_resp.txt')
	datos_temp={}
	datos={}
	llaves={}
	final=0
	i=0
	j=0

	for linea in archivo.readlines():
		datos_temp[i]=list(linea)
		final=len(datos_temp[i])-1
		if datos_temp[i][final] == "\n":
			datos_temp[i].pop()
		i+=1
	
	for elemento in datos_temp:
		llaves[j]=[]
		for numero in xrange(rangos_resp['tipo_exa'][0],rangos_resp['tipo_exa'][1]):
			llaves[j].append(datos_temp[elemento][numero])
		j+=1
	j=0

	for elemento in llaves:
		llaves[elemento] = ''.join(llaves[elemento])

	for elemento in datos_temp:
		datos[llaves[elemento]]=[]
		for numero in xrange(rangos_resp['respuestas'][0],rangos_resp['respuestas'][1]):
			datos[llaves[elemento]].append(datos_temp[elemento][numero])

	return datos

def obtener_respuestas_elim():
	archivo = open("Elem_Evaluacion/resp_elim.txt","r")
	archivo.seek(0)
	
	datos_temp={}
	datos={}
	llaves={}
	resp_elim={}
	final=0
	i=0
	j=0

	for linea in archivo.readlines():
		datos_temp[i]=list(linea)
		final=len(datos_temp[i])-1
		if datos_temp[i][final] == "\n":
			datos_temp[i].pop()
		i+=1

	for elemento in datos_temp:
		datos_temp[elemento] = ''.join(datos_temp[elemento])

	for elemento in datos_temp:
		datos_temp[elemento]=datos_temp[elemento].split('*')
		datos[datos_temp[elemento][0]]=datos_temp[elemento]

	for elemento in datos:
		datos[elemento].pop(0)
		for num in xrange(0,len(datos[elemento])):
			datos[elemento][num]= int(datos[elemento][num])-1
	return datos

def obtener_rangos_archivo(nom_arch):

	archivo = open(nom_arch,"r")
	archivo.seek(0)
	
	datos_temp={}
	datos={}
	llaves={}
	areas_exa={}
	final=0
	i=0
	j=0

	for linea in archivo.readlines():
		datos_temp[i]=list(linea)
		final=len(datos_temp[i])-1
		if datos_temp[i][final] == "\n":
			datos_temp[i].pop()
		i+=1

	for num in xrange(0,len(datos_temp)):
		datos_temp[num]=''.join(datos_temp[num])
		datos_temp[num]=datos_temp[num].split('*')
		areas_exa[datos_temp[num][0]]=datos_temp[num][1]
	
	for elemento in areas_exa:
		areas_exa[elemento]=areas_exa[elemento].split('-')
		areas_exa[elemento][0] = int(areas_exa[elemento][0])-1
		areas_exa[elemento][1] = int(areas_exa[elemento][1])
	return areas_exa

def retira_eliminadas(respuestas,eliminadas):
	i=0
	j=0

	for elemento in respuestas:
		for elem in respuestas[elemento]:
			if elemento in eliminadas and i<=len(eliminadas[elemento])-1:
				if eliminadas[elemento][i] == j:
					respuestas[elemento][j] = 'Z'
					i+=1
				j+=1
		i=0
		j=0
		
def compara_respuestas(alumnos_ex,alumnos_r,respuestas,resp_elim):

	resp_correc=[]
	diccionario_calificaciones={}
	calificaciones={}
	tipo_exa_alum=[]
	cantidad_resp_correc=0
	i=0
	j=0
	eliminadas = 0
	sin_contestar = 0

	for exa in alumnos_ex: #se obtiene un arreglo de la siguiente forma ['2B','2B']
		tipo_exa_alum.append(alumnos_ex[exa])

	for elemento in alumnos_r:
		for elem in alumnos_r[elemento]:
			if elem == 'X':
					sin_contestar+=1
			if (elem==respuestas[alumnos_ex[elemento]][i] and respuestas[alumnos_ex[elemento]][i]!='Z'):
				resp_correc.append(elem)
			i+=1
		i=0

		if alumnos_ex[elemento] in resp_elim:
			eliminadas = len(resp_elim[alumnos_ex[elemento]])
		else:
			eliminadas=0

		nom_arch = list(alumnos_ex[elemento])
		nom_arch = 'Tipos_Examen/exa_tipo_'+nom_arch[0]+'.txt'
		rangos_areas=obtener_rangos_archivo(nom_arch)
		resp_elim=obtener_respuestas_elim()
		# calificaciones = areas_examenes(alumnos_ex[elemento],alumnos_r[elemento],respuestas,rangos_areas,resp_elim)
		cantidad_resp_correc = len(resp_correc)
		calificacion=(float(len(resp_correc))/float(len(respuestas[alumnos_ex[elemento]])-eliminadas))*10
		calificaciones['promedio_dec']=round(calificacion,4)
		calificaciones['resp_correc']=cantidad_resp_correc
		calificaciones['resp_incorrec']=len(respuestas[alumnos_ex[elemento]])-cantidad_resp_correc-eliminadas-sin_contestar
		calificaciones['tipo_examen']=tipo_exa_alum[j]
		calificaciones['sin_contestar']=sin_contestar
		diccionario_calificaciones[elemento]=calificaciones
		calificaciones={}
		resp_correc=[]
		eliminadas=0
		sin_contestar=0
		j+=1
	j=0
	
	return diccionario_calificaciones

def areas_examenes(num_examen,alum_res,respuestas,rangos_areas,resp_elim):
	resp_correc=[]
	array_rangos=[]
	alum_calif={}
	calificaciones={}
	rangos_areas_temp = rangos_areas
	prueba = []

	for elemento in rangos_areas:
		rangos_areas[elemento]=list(xrange(rangos_areas[elemento][0],rangos_areas[elemento][1]))

	for elemento in rangos_areas:
		for num in rangos_areas[elemento]:
			if (alum_res[num]==respuestas[num_examen][num] and alum_res[num]!='Z'):
				resp_correc.append(alum_res[num])
			if num_examen in resp_elim:
				for elem in resp_elim[num_examen]:
					if (num==elem):
						indice=rangos_areas_temp[elemento].index(elem)
						rangos_areas_temp[elemento][indice]='-'
					
		rangos_areas_temp[elemento] = [x for x in rangos_areas_temp[elemento] if x != '-']
		calif=(float(len(resp_correc))/float(len(rangos_areas_temp[elemento])))*10
		calificaciones[elemento]=round(calif,4)
		if num_examen in resp_elim:
			eliminadas = len(resp_elim[num_examen])
		else:
			eliminadas=0
		num_resp_correc = float(len(respuestas[num_examen])-eliminadas)
		ponderacion=float(len(rangos_areas_temp[elemento]))/num_resp_correc
		calificaciones[elemento+'_pond']=round(calif*ponderacion,4)
		resp_correc=[]
	return calificaciones

def escribe_en_excel(datos_alumnos,nombre,tipos_examen,salida):

	datos_a_imp={}
	temp={}
	encabezado_xls=[]
	k=0
	for elemento in tipos_examen:
		for elem in datos_alumnos:
			tipo_1=list(elemento)
			tipo_1=tipo_1[0]
			tipo_2=list(datos_alumnos[elem]['tipo_examen'])
			tipo_2=tipo_2[0]
			if (tipo_1==tipo_2):
				temp[elem]=datos_alumnos[elem]
				datos_a_imp[tipo_1]=temp
		temp={}
	for elemento in datos_a_imp:
		libro=xlwt.Workbook()
		hoja=libro.add_sheet("Calificaciones")
		i=1
		j=1
		fila=hoja.row(0)
		for elem in datos_a_imp[elemento]:
			for el in datos_a_imp[elemento][elem]:
				if (k==0):
					encabezado_xls=datos_a_imp[elemento][elem].keys()
					encabezado_xls.append('alumno')
					encabezado_xls.sort()
					len_encabezado=len(encabezado_xls)
					for x in xrange(0,len_encabezado):
						fila.write(x,encabezado_xls[x])
				k+=1
			fila=hoja.row(i)
			fila.write(0,elem)
			for columna in encabezado_xls:
				if (columna!='alumno'):
					fila.write(j,datos_alumnos[elem][columna])
					j+=1
			j=1
			i+=1
		i=0
		j=0
		k=0
		nombre_xls=salida+nombre+"_evaluado_examen_"+elemento+".xls"
		libro.save(nombre_xls)

def main():
	if len(sys.argv)<2:
		nombre=raw_input("Ingresa el nombre del archivo de resultados de los alumnos: ")
		salida=raw_input("Archivo de salida: ")
	elif len(sys.argv)<3:
		salida=raw_input("Archivo de salida: ")
	else:
		nombre = sys.argv[1]
		salida = sys.argv[2]		
	califica(nombre, salida)

if __name__ == "__main__":
    main()
