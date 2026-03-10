LENGGWAHElang.
Group Members:
Cuyacot, Joemari
Dele Cruz, Mitz
Jacaba, Arvin
Gilbuena, Valerie (absent)
Mojello, Anne (absent)
Morada, Mark joseph

1. LIST OF ACCEPTABLE CHARACTERS (joms)
A. Letters
 • a to z
 • A to Z
 • Unicode letters (UTF-8 supported, including ñ, á, etc.)
B. Digits
 • 0 to 9 
C. Special Characters
 _ ! ? = + - * / % & | ^ ~
 < > ( ) { } [ ]
 . , : ; @ $ #
 " ' ` \
D. Whitespace
 • Space
 • Tab
 • Newline

    2. LIST OF TOKENS (DEFINED) (joms)
A token is the smallest meaningful unit in a LENGGWAHELang program.
Token Categories:
    1. Pangalan (Identifiers)
    2. Susing-Salita (Keywords)
    3. Halaga (Literals)
    4. Operador (Operators)
    5. Hangganan (Delimiters)
    6. Komento (Comments)
    7. Puting-Espasyo(Whitespace)


3. GENERAL RULES FOR TOKENS (joms)
    • Case-sensitive ang LENGGWAHELang.
    • Tokens are separated by whitespace or delimiters.
    • Katulong are reserved and cannot be used as identifiers.
    • Comments start with # and end at newline.
    • Identifiers cannot begin with digits.
    • Method names may end with ?, !, or =.

4. CHARACTER SET FOR IDENTIFIER TOKENS (mitz) 
Core Character Set:
 • Letters
 • Digits
 • Underscore (_)
Additional Prefix/Suffix Symbols:
 @@ $ ! ? = 

    5. IDENTIFIER RULES (mitz)
A.(Local Variables / Functions)
Character Set:
 { letters, digits, underscore }
Rules:
    1. Must start with lowercase letter or underscore.
    2. Remaining characters may contain letters, digits, underscore.
    3. Function names may end with ?, !, or =.
    4. Must not be a reserved keyword.
Example:
 bilang
 pagkuha_data
 totoo?

B. (Constants)
Rules:
    1. Must start with uppercase letter.
    2. Used for permanent values or structure names.
Example:
 PI
 PAGKUHA_DATA

C. Instance Variables
Rule:
 Must start with @ followed by valid identifier.
Example:
 @pangalan
 @edad

D. Class Variables
Rule:
 Must start with @@ followed by valid identifier.
Example:
 @@bilang_lahat

E. Global Variables
Rule:
 Must start with $ followed by identifier.
Example:
 $oras
 $estado


    6. RESERVED KEYWORDS (KATULONG) (morada)
These cannot be used as identifiers.
    • Simula 		-	begin program
    • Wakas 		-	end block
    • Gawain 		-	define function
    • Uri 			-	class
    • Kung 			-	if
    • KungHindi 		-	else
    • KungHindiKung 	- 	elsif
    • Para 			- 	for loop
    • Habang 		-	while
    • Gawin_Habang 	- 	do While
    • Ibalik 			- 	return
    • Totoo 			-	true
    • Mali 			- 	false
    • Wala 			-	null
    • Subukan 		-	try
    • Saluhin 		- 	rescue
    • SaWakas 		- 	ensure
    • Tuloy 			- 	continue
    • Hinto 			- 	break
    • Sarili 			- 	self 
    • Ipakita 		- 	Show                                                                                                            
    • Buong_Numero 	- 	int
    • Lumulutang 		- 	float
    • Bool 			-	bool
    • Tali 			- 	string	
    • Para 			- 	For
    • Bago 			- 	While
    • Gawin_Bago 		-	Do While



    7. Puting-Espasyo (WHITE SPACE) (arvin)
 • Space
 • Tab
 • Newline
Used to separate tokens. 
 Indentation optional but recommended for readability.

    8. HANGGANAN (DELIMITERS) (arvin)
(( )) (una huli) 		Parintisis - grouping 
{{ }} {una huli} 		Kurly Brays - single line block
[[ ]] [una  huli] 		Squayre Brakits - array
 ,, kama - 		separator
 .. dat - 		call of object
 :: kolon - 		hash key o symbol
Dina gagamitan ng semi colon for end statement - programmer nakakalimutan ito
 ;; semi kolon - 	separator of multiple elements


    9. OPERADOR (OPERATORS) (arvin)
Dag2 ++ 	Increment Operator
Bawas2 - - 	Decrement Operator
Bawal_yan +- 	Invalid
Dagsaym += 	Assignment Operator
A. Mathematical (Matematika)
    • Adisyon +
    • Sobtrak -
    • Tayms *
 / Dibayd
 % modyulus 
 ** kapangyarihan (power)
B. Logical (Lohika)
&& at 		 - AND
 || o 		 - OR
 ! hindi 		 - NOT
C. Relational (Paghahambing)
< mas_maliit 					(less than)
 <= hindi_maliit_saks_lang 			(less than equal)
> mas_malaki 					(greater than)
 => hindi_malaki_saks_lang 			(greater than equal)
 <=> paghambing_gusto_ko_ng_lambing 	(compare)
D. Equality (Pagkakapareho)
== 	saym
 !=	hindi_saym


10.	BASIC SYNTAX STYLE (EASY TO MEMORIZE) (morada)
FUNCTION
Simula
 Gawain  Tali bati(Tali pangalan){
	Ibalik "Kamusta, " Adisyon pangalan
}
Wakas   

CONDITIONAL
Simula
Buong_Numero edad saym 19
Kung (edad hindi_malaki_saks_lang 18){
	Ipakita "Pwede"
} KungHindi {
	ipakita "Hindi pa pwede"
}
Wakas

LOOPS 
Simula
 Para bilang saym 1; bilang mas_maliit 5; bilang dag2 {
 	Ipakita bilang
}
 Wakas
