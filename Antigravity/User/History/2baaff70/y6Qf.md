# LENGGWAHELang — Lexical Analyzer Token Rules

> A Filipino-based programming language combining the best of **Python** and **Ruby**.

---

## 1. Character Set (Katangian ng Karakter)

The lexer accepts the following raw characters from source files encoded in **UTF-8**:

| Category         | Characters                                                       |
| ---------------- | ---------------------------------------------------------------- |
| **Letters**      | `a`–`z`, `A`–`Z`, Unicode letters (ñ, á, etc.)                  |
| **Digits**       | `0`–`9`                                                          |
| **Special**      | `_ ! ? = + - * / % & \| ^ ~ < > ( ) { } [ ] . , : ; @ $ # " ' \`` ` \\ ` |
| **Whitespace**   | Space (` `), Tab (`\t`), Newline (`\n`, `\r\n`)                  |

---

## 2. Token Categories (Mga Uri ng Token)

Every valid sequence of characters in a LENGGWAHELang source file must be classified into exactly **one** of the following token types:

| #  | Token Type                       | Filipino Name     | Description                      |
| -- | -------------------------------- | ----------------- | -------------------------------- |
| 1  | Identifier                       | Pangalan          | Names for variables, functions, classes |
| 2  | Keyword                          | Susing-Salita     | Reserved words with special meaning |
| 3  | Literal                          | Halaga            | Constant values (numbers, strings, booleans) |
| 4  | Operator                         | Operador          | Arithmetic, logical, relational symbols |
| 5  | Delimiter                        | Hangganan         | Punctuation that structures code |
| 6  | Comment                          | Komento           | Ignored text for documentation   |
| 7  | Whitespace                       | Puting-Espasyo    | Spaces, tabs, newlines (skipped) |

---

## 3. General Lexing Rules

1. **Case-sensitive** — `Kung` (keyword) ≠ `kung` (identifier).
2. **Maximal munch** — The lexer always consumes the **longest** valid token. e.g. `<=` is one token, not `<` + `=`.
3. **Token separation** — Tokens are separated by whitespace or delimiters.
4. **Keyword priority** — If an identifier matches a reserved keyword, it is classified as a **keyword**.
5. **No semicolons** — Statements are terminated by **newlines** (Python-style). Semicolons (`;`) are only used as element separators.
6. **Comments** — Start with `#` and extend to end of line (Python & Ruby style).
7. **Identifiers cannot start with digits.**
8. **Method names** may end with `?`, `!`, or `=` (Ruby-style).

---

## 4. Token Definitions & Regex Patterns

### 4.1 Susing-Salita (Keywords)

Keywords are **reserved** and cannot be used as identifiers. The lexer must match these **before** identifiers.

| Keyword            | Meaning (English) | Origin Inspiration |
| ------------------ | ------------------ | ------------------ |
| `Simula`           | begin program      | —                  |
| `Wakas`            | end block           | —                  |
| `Gawain`           | def (define function) | Ruby `def` / Python `def` |
| `Uri`              | class              | Ruby `class` / Python `class` |
| `Kung`             | if                 | Python `if` / Ruby `if` |
| `KungHindi`        | else               | Python `else`      |
| `KungHindiKung`    | elsif / elif       | Ruby `elsif` + Python `elif` |
| `Para`             | for loop           | Python `for`       |
| `Habang`           | while              | Python `while` / Ruby `while` |
| `Gawin_Habang`     | do...while         | Ruby `do...while`  |
| `Ibalik`           | return             | Python `return` / Ruby `return` |
| `Totoo`            | true               | Ruby `true`        |
| `Mali`             | false              | Ruby `false`       |
| `Wala`             | nil / None         | Ruby `nil` + Python `None` |
| `Subukan`          | try                | Python `try` / Ruby `begin` |
| `Saluhin`          | rescue / except    | Ruby `rescue` + Python `except` |
| `SaWakas`          | ensure / finally   | Ruby `ensure` + Python `finally` |
| `Tuloy`            | continue / next    | Python `continue` / Ruby `next` |
| `Hinto`            | break              | Python `break` / Ruby `break` |
| `Sarili`           | self               | Python `self` / Ruby `self` |
| `Ipakita`          | print / puts       | Python `print()` / Ruby `puts` |
| `Buong_Numero`     | int                | Python `int`       |
| `Lumulutang`       | float              | Python `float`     |
| `Bool`             | bool               | Python `bool`      |
| `Tali`             | str / string       | Python `str`       |
| `at`               | and                | Python `and`       |
| `o`                | or                 | Python `or`        |
| `hindi`            | not                | Python `not`       |
| `sa`               | in (for iteration) | Python `in`        |
| `Angkat`           | import             | Python `import`    |
| `Mula`             | from (import)      | Python `from`      |
| `Bilang`           | as (alias)         | Python `as`        |
| `Magmana`          | inherits / extends | Ruby `<` / Python inheritance |
| `Tanggapin`        | yield              | Python `yield` / Ruby `yield` |
| `Gamit`            | with (context mgr) | Python `with`      |
| `Bago`             | new (instantiate)  | Ruby `.new`        |

**Regex pattern (keyword check):**
```
KEYWORDS = {
    "Simula", "Wakas", "Gawain", "Uri", "Kung", "KungHindi",
    "KungHindiKung", "Para", "Habang", "Gawin_Habang", "Ibalik",
    "Totoo", "Mali", "Wala", "Subukan", "Saluhin", "SaWakas",
    "Tuloy", "Hinto", "Sarili", "Ipakita", "Buong_Numero",
    "Lumulutang", "Bool", "Tali", "at", "o", "hindi", "sa",
    "Angkat", "Mula", "Bilang", "Magmana", "Tanggapin", "Gamit", "Bago"
}
```
> After matching an identifier, check if it exists in the keyword set.

---

### 4.2 Pangalan (Identifiers)

Identifiers name variables, functions, classes, and modules.

#### A. Local Variables & Functions
```
Pattern:  [a-z_][a-zA-Z0-9_]*[?!=]?
Examples: bilang, pagkuha_data, totoo?, i_save!, halaga=
```
- Must start with **lowercase letter** or **underscore**.
- May contain letters, digits, underscores.
- May end with `?` (query), `!` (destructive), or `=` (setter) — *Ruby-style*.

#### B. Constants
```
Pattern:  [A-Z][A-Z0-9_]*
Examples: PI, MAX_LAKI, PAGKUHA_DATA
```
- Must start with an **uppercase letter**.
- Remaining characters: uppercase letters, digits, underscores.

#### C. Instance Variables (Ruby-style)
```
Pattern:  @[a-z_][a-zA-Z0-9_]*
Examples: @pangalan, @edad, @bilang
```
- Prefix `@` followed by a valid local identifier.

#### D. Class Variables (Ruby-style)
```
Pattern:  @@[a-z_][a-zA-Z0-9_]*
Examples: @@bilang_lahat, @@kabuuan
```
- Prefix `@@` followed by a valid local identifier.

#### E. Global Variables (Ruby-style)
```
Pattern:  \$[a-zA-Z_][a-zA-Z0-9_]*
Examples: $oras, $estado, $VERSION
```
- Prefix `$` followed by a valid identifier.

---

### 4.3 Halaga (Literals)

#### A. Integer Literals (Buong Numero)
```
Pattern:  [0-9][0-9_]*
Examples: 0, 42, 1_000_000
```
- Sequence of digits, underscores allowed as visual separators (Python & Ruby style).
- No leading zeros except for the number `0` itself.

#### B. Float Literals (Lumulutang)
```
Pattern:  [0-9][0-9_]*\.[0-9][0-9_]*
Examples: 3.14, 0.5, 1_000.50
```
- Must have digits on **both** sides of the decimal point.

#### C. String Literals (Tali)
```
Double-quoted:  "([^"\\]|\\.)*"
Single-quoted:  '([^'\\]|\\.)*'
```
- **Double-quoted** (`"..."`) — supports escape sequences and string interpolation `#{expr}` (Ruby-style).
- **Single-quoted** (`'...'`) — raw strings, only `\\` and `\'` are escaped.

**Escape sequences** (inside double-quoted strings):

| Escape   | Meaning          |
| -------- | ---------------- |
| `\n`     | Newline          |
| `\t`     | Tab              |
| `\\`     | Backslash        |
| `\"`     | Double quote     |
| `\#`     | Literal `#`      |
| `\0`     | Null character   |

**String Interpolation** (Ruby-style, double-quoted only):
```
"Kamusta, #{pangalan}!"
```

#### D. Boolean Literals
```
Totoo    →  true
Mali     →  false
```
> Matched as **keywords**, not separate literal tokens.

#### E. Null Literal
```
Wala     →  nil / None
```
> Matched as a **keyword**.

---

### 4.4 Operador (Operators)

Operators are matched using **maximal munch** (longest match first).

#### Tokenization Priority Order (longest first):

| Token   | Name (Filipino)              | Type             | Precedence |
| ------- | ---------------------------- | ---------------- | ---------- |
| `**`    | Kapangyarihan                | Arithmetic       | 1 (highest)|
| `<<=>`  | —                            | *(invalid)*      | —          |
| `<=>`   | Paghambing                   | Comparison       | 6          |
| `<=`    | Hindi_Maliit_Saks_Lang       | Relational       | 6          |
| `>=`    | Hindi_Malaki_Saks_Lang       | Relational       | 6          |
| `==`    | Saym                         | Equality         | 7          |
| `!=`    | Hindi_Saym                   | Equality         | 7          |
| `&&`    | At (AND)                     | Logical          | 11         |
| `\|\|`  | O (OR)                       | Logical          | 12         |
| `+=`    | Dagsaym                      | Assignment       | 14         |
| `-=`    | Bawassaym                    | Assignment       | 14         |
| `*=`    | Taymssaym                    | Assignment       | 14         |
| `/=`    | Dibaydsaym                   | Assignment       | 14         |
| `%=`    | Modyulussaym                 | Assignment       | 14         |
| `++`    | Dag2 (Increment)             | Unary            | 2          |
| `--`    | Bawas2 (Decrement)           | Unary            | 2          |
| `+`     | Adisyon                      | Arithmetic       | 4          |
| `-`     | Sobtrak                      | Arithmetic       | 4          |
| `*`     | Tayms                        | Arithmetic       | 3          |
| `/`     | Dibayd                       | Arithmetic       | 3          |
| `%`     | Modyulus                     | Arithmetic       | 3          |
| `<`     | Mas_Maliit                   | Relational       | 6          |
| `>`     | Mas_Malaki                   | Relational       | 6          |
| `!`     | Hindi (NOT)                  | Logical (unary)  | 2          |
| `=`     | Saym (Assignment)            | Assignment       | 14         |

#### Operator Regex (ordered longest → shortest):
```
OPERATOR_PATTERN = r'(\*\*|<=>|<=|>=|==|!=|&&|\|\||[+\-*/%]=|[+]{2}|[-]{2}|[+\-*/%<>=!])'
```

---

### 4.5 Hangganan (Delimiters)

| Token | Name (Filipino)     | Purpose                        |
| ----- | ------------------- | ------------------------------ |
| `(`   | Parintisis Una      | Open grouping / function call  |
| `)`   | Parintisis Huli     | Close grouping / function call |
| `{`   | Kurly Una           | Open block                     |
| `}`   | Kurly Huli          | Close block                    |
| `[`   | Brakits Una         | Open array / index             |
| `]`   | Brakits Huli        | Close array / index            |
| `,`   | Kama                | Separator                      |
| `.`   | Tuldok              | Method call / member access    |
| `:`   | Kolon               | Hash key / symbol              |
| `;`   | Semi Kolon          | Multi-element separator        |
| `\n`  | Bagong Linya        | Statement terminator           |

**Delimiter Regex:**
```
DELIMITER_PATTERN = r'[(){}\[\],.;:\n]'
```

---

### 4.6 Komento (Comments)

```
Pattern:  #[^\n]*
```
- Starts with `#`, consumes everything until end of line.
- Comments are **discarded** by the lexer (not emitted as tokens).

**Example:**
```
# Ito ay isang komento
Buong_Numero x saym 10  # inline na komento
```

---

### 4.7 Puting-Espasyo (Whitespace)

```
Pattern:  [ \t\r]+
```
- Spaces, tabs, and carriage returns are **skipped** by the lexer.
- **Newlines** (`\n`) are significant — they act as **statement terminators** (Python-style).
- Consecutive newlines are collapsed into a single `NEWLINE` token.
- Newlines inside `()`, `[]`, or `{}` are **ignored** (implicit line continuation, Python-style).

---

## 5. Token Precedence (Lexer Match Order)

The lexer must attempt to match tokens in this **exact order** to avoid ambiguity:

```
1. Whitespace          →  skip (but emit NEWLINE for \n)
2. Comments            →  # ... (discard)
3. String Literals     →  "..." or '...'
4. Numeric Literals    →  digits, with optional . for floats
5. Operators           →  longest match first (**, <=>, <=, etc.)
6. Delimiters          →  ( ) { } [ ] , . : ;
7. Identifiers/Keywords →  match identifier, then check keyword set
8. Unknown Character   →  emit ERROR token
```

---

## 6. Token Output Format

Each token emitted by the lexer should contain:

```
Token {
    type:    TOKEN_TYPE,     # e.g., KEYWORD, IDENTIFIER, INTEGER, etc.
    value:   "raw_value",    # the actual matched string
    line:    line_number,    # 1-indexed
    column:  column_number   # 1-indexed
}
```

**Example tokenization of:**
```
Simula
  Buong_Numero edad saym 19
  Ipakita edad
Wakas
```

| Type          | Value          | Line | Column |
| ------------- | -------------- | ---- | ------ |
| KEYWORD       | `Simula`       | 1    | 1      |
| NEWLINE       | `\n`           | 1    | 7      |
| KEYWORD       | `Buong_Numero` | 2    | 3      |
| IDENTIFIER    | `edad`         | 2    | 16     |
| OPERATOR      | `saym` (`=`)   | 2    | 21     |
| INTEGER       | `19`           | 2    | 26     |
| NEWLINE       | `\n`           | 2    | 28     |
| KEYWORD       | `Ipakita`      | 3    | 3      |
| IDENTIFIER    | `edad`         | 3    | 11     |
| NEWLINE       | `\n`           | 3    | 15     |
| KEYWORD       | `Wakas`        | 4    | 1      |
| NEWLINE       | `\n`           | 4    | 6      |
| EOF           | —              | 5    | 1      |

---

## 7. Error Handling

The lexer should handle the following error cases:

| Error                        | Example           | Action                         |
| ---------------------------- | ----------------- | ------------------------------ |
| Unterminated string          | `"kamusta...`     | Emit `ERROR` token, report line/col |
| Invalid character            | `~` in wrong context | Emit `ERROR` token, continue   |
| Invalid number format        | `007`, `1.`       | Emit `ERROR` token             |
| Identifier starts with digit | `2pangalan`       | Emit `ERROR` token             |
| Invalid operator combo       | `+-`              | Emit `+` then `-` separately   |

---

## 8. Summary of Python + Ruby Features Used

| Feature                         | From Python             | From Ruby                  |
| ------------------------------- | ----------------------- | -------------------------- |
| `#` single-line comments        | ✅                      | ✅                         |
| Newline as statement terminator | ✅                      |                            |
| Implicit line continuation      | ✅ (inside brackets)    |                            |
| Underscore in numbers           | ✅ `1_000`              | ✅ `1_000`                 |
| `@` instance variables          |                         | ✅                         |
| `@@` class variables            |                         | ✅                         |
| `$` global variables            |                         | ✅                         |
| `?` `!` `=` method suffixes    |                         | ✅                         |
| String interpolation `#{}`      |                         | ✅                         |
| `**` exponent operator          | ✅                      | ✅                         |
| `<=>` spaceship operator        |                         | ✅                         |
| `rescue` / `ensure` style       |                         | ✅ (via Saluhin/SaWakas)   |
| `try`/`except` conceptual flow  | ✅ (via Subukan)        |                            |
| `self` keyword                  | ✅                      | ✅                         |
| `yield` keyword                 | ✅                      | ✅                         |
| `with` context manager          | ✅ (via Gamit)          |                            |
| `import` / `from` system        | ✅ (via Angkat/Mula)    |                            |
