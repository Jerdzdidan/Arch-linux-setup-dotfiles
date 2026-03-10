# RuPy — Lexical Analyzer Token Rules

> **RuPy** is a hybrid programming language that combines the lexical rules of **Python** and **Ruby** into a single, unified specification.

---

## 1. LIST OF ACCEPTABLE CHARACTERS

### A. Letters
- `a` to `z` (lowercase)
- `A` to `Z` (uppercase)
- Unicode letters (UTF-8 supported)

### B. Digits
- `0` to `9`

### C. Special Characters
```
_ ! ? = + - * / % & | ^ ~
< > ( ) { } [ ]
. , : ; @ $ #
" ' ` \
```

### D. Whitespace
- Space (` `)
- Tab (`\t`)
- Newline (`\n`, `\r\n`)

---

## 2. LIST OF TOKENS (DEFINED)

A token is the smallest meaningful unit in a RuPy program.

### Token Categories:
1. **Identifiers** — Names for variables, functions, classes, modules
2. **Keywords** — Reserved words with special meaning
3. **Literals** — Constant values (numbers, strings, booleans, symbols, regex)
4. **Operators** — Arithmetic, logical, relational, assignment symbols
5. **Delimiters** — Punctuation that structures code
6. **Comments** — Ignored text for documentation
7. **Whitespace** — Spaces, tabs, newlines

---

## 3. GENERAL RULES FOR TOKENS

- **Case-sensitive** — `Class` ≠ `class` ≠ `CLASS`.
- Tokens are separated by **whitespace** or **delimiters**.
- Keywords are **reserved** and cannot be used as identifiers.
- Identifiers **cannot begin with digits**.
- The lexer uses **maximal munch** — always consumes the longest valid token.
  - e.g., `<=` is one `LESS_EQUAL` token, not `<` followed by `=`.
- **Newlines** are significant as statement terminators (from Python).
- **Method names** may end with `?`, `!`, or `=` (from Ruby).
- Comments start with `#` and extend to end of line (shared by Python and Ruby).

---

## 4. CHARACTER SET FOR IDENTIFIER TOKENS

### Core Character Set:
- Letters (`a-z`, `A-Z`, Unicode letters)
- Digits (`0-9`)
- Underscore (`_`)

### Additional Prefix/Suffix Symbols (from Ruby):
| Symbol | Usage                  |
| ------ | ---------------------- |
| `@`    | Instance variable prefix |
| `@@`   | Class variable prefix  |
| `$`    | Global variable prefix |
| `?`    | Method name suffix (predicate) |
| `!`    | Method name suffix (destructive/bang) |
| `=`    | Method name suffix (setter) |

---

## 5. IDENTIFIER RULES

### A. Local Variables / Functions (Python + Ruby)
**Character Set:** `{ letters, digits, underscore }`

**Rules:**
1. Must start with a **lowercase letter** or **underscore** (`_`).
2. Remaining characters: letters, digits, underscores.
3. Function/method names may end with `?`, `!`, or `=` (from Ruby).
4. Must not be a reserved keyword.
5. By convention, `_single_leading_underscore` indicates internal use (from Python).
6. `__double_leading_underscore` triggers name mangling in classes (from Python).
7. `__dunder__` style is reserved for special/magic methods (from Python).

**Regex:** `[a-z_][a-zA-Z0-9_]*[?!=]?`

**Examples:**
```
count
_private_var
get_data
empty?
save!
name=
__init__
```

### B. Constants (Python + Ruby)
**Rules:**
1. All uppercase letters with underscores (from Python convention and Ruby rule).
2. In Ruby, constants also start with a single uppercase letter — RuPy follows this:
   any identifier starting with an uppercase letter is treated as a constant or class name.

**Regex:** `[A-Z][a-zA-Z0-9_]*`

**Examples:**
```
PI
MAX_SIZE
VERSION
MyClass
```

### C. Instance Variables (from Ruby)
**Rule:** Must start with `@` followed by a valid local identifier.

**Regex:** `@[a-z_][a-zA-Z0-9_]*`

**Examples:**
```
@name
@age
@_internal
```

### D. Class Variables (from Ruby)
**Rule:** Must start with `@@` followed by a valid local identifier.

**Regex:** `@@[a-z_][a-zA-Z0-9_]*`

**Examples:**
```
@@count
@@total_instances
```

### E. Global Variables (from Ruby)
**Rule:** Must start with `$` followed by a valid identifier.

**Regex:** `\$[a-zA-Z_][a-zA-Z0-9_]*`

**Examples:**
```
$stdout
$debug_mode
$LOAD_PATH
```

---

## 6. RESERVED KEYWORDS

These words are reserved and **cannot** be used as identifiers.

### Control Flow (from Python + Ruby)
| Keyword    | Meaning                        | Origin     |
| ---------- | ------------------------------ | ---------- |
| `if`       | Conditional                    | Both       |
| `elif`     | Else-if branch                 | Python     |
| `else`     | Default branch                 | Both       |
| `unless`   | Negated conditional            | Ruby       |
| `for`      | For loop                       | Both       |
| `in`       | Membership / iteration         | Both       |
| `while`    | While loop                     | Both       |
| `until`    | Negated while loop             | Ruby       |
| `do`       | Block opener                   | Ruby       |
| `break`    | Exit loop                      | Both       |
| `continue` | Skip to next iteration         | Python     |
| `next`     | Skip to next iteration (alias) | Ruby       |
| `pass`     | No-op placeholder              | Python     |
| `return`   | Return from function           | Both       |

### Definitions
| Keyword   | Meaning                  | Origin     |
| --------- | ------------------------ | ---------- |
| `def`     | Define function/method   | Both       |
| `class`   | Define class             | Both       |
| `module`  | Define module            | Ruby       |
| `lambda`  | Anonymous function       | Both       |
| `yield`   | Generator / block yield  | Both       |

### Exception Handling
| Keyword   | Meaning                  | Origin     |
| --------- | ------------------------ | ---------- |
| `try`     | Begin try block          | Python     |
| `except`  | Catch exception          | Python     |
| `rescue`  | Catch exception (alias)  | Ruby       |
| `finally` | Always-execute block     | Python     |
| `ensure`  | Always-execute (alias)   | Ruby       |
| `raise`   | Raise exception          | Both       |
| `begin`   | Begin exception block    | Ruby       |
| `end`     | End block                | Ruby       |

### Logical & Values
| Keyword  | Meaning            | Origin     |
| -------- | ------------------ | ---------- |
| `and`    | Logical AND (word) | Both       |
| `or`     | Logical OR (word)  | Both       |
| `not`    | Logical NOT (word) | Both       |
| `true`   | Boolean true       | Ruby       |
| `True`   | Boolean true       | Python     |
| `false`  | Boolean false      | Ruby       |
| `False`  | Boolean false      | Python     |
| `nil`    | Null value         | Ruby       |
| `None`   | Null value         | Python     |
| `is`     | Identity check     | Python     |
| `self`   | Current instance   | Both       |

### Modules & Scope
| Keyword    | Meaning                 | Origin     |
| ---------- | ----------------------- | ---------- |
| `import`   | Import module           | Python     |
| `from`     | Import from             | Python     |
| `as`       | Alias                   | Python     |
| `require`  | Load file               | Ruby       |
| `include`  | Mixin module            | Ruby       |
| `global`   | Global scope marker     | Python     |
| `nonlocal` | Enclosing scope marker  | Python     |

### Other
| Keyword    | Meaning                 | Origin     |
| ---------- | ----------------------- | ---------- |
| `with`     | Context manager         | Python     |
| `async`    | Async function          | Python     |
| `await`    | Await coroutine         | Python     |
| `del`      | Delete reference        | Python     |
| `assert`   | Debug assertion         | Python     |
| `attr`     | Attribute accessor      | Ruby       |

---

## 7. WHITESPACE (Puting-Espasyo)

- **Space** (` `) — separates tokens, skipped by lexer.
- **Tab** (`\t`) — separates tokens, skipped by lexer.
- **Newline** (`\n`) — acts as a **statement terminator** (from Python).
  - Consecutive newlines collapse into a single `NEWLINE` token.
  - Newlines inside `()`, `[]`, or `{}` are **ignored** (implicit line continuation — from Python).
  - Explicit line continuation with `\` at end of line (from Python).
- **Indentation** is **optional** but recommended for readability.
  - Blocks are defined by `do...end`, `{...}`, or `:` + indented block (see syntax style).

---

## 8. DELIMITERS

| Token | Name              | Purpose                             | Origin |
| ----- | ----------------- | ----------------------------------- | ------ |
| `(`   | Left Paren        | Grouping, function call args        | Both   |
| `)`   | Right Paren       | Close grouping                      | Both   |
| `{`   | Left Brace        | Block, hash/dict literal            | Both   |
| `}`   | Right Brace       | Close block/hash                    | Both   |
| `[`   | Left Bracket      | Array/list, indexing                | Both   |
| `]`   | Right Bracket     | Close array/list                    | Both   |
| `,`   | Comma             | Separator                           | Both   |
| `.`   | Dot               | Method call, member access          | Both   |
| `:`   | Colon             | Hash key, slice, block start        | Both   |
| `;`   | Semicolon         | Multiple statements on one line     | Both   |
| `\n`  | Newline           | Statement terminator                | Python |
| `->`  | Arrow             | Lambda / return type annotation     | Python |
| `=>`  | Hash Rocket       | Hash key-value pairing              | Ruby   |
| `|`   | Pipe (in blocks)  | Block parameter delimiter           | Ruby   |

**Delimiter Regex:**
```
[(){}\[\],.;:\n]|->|=>
```

---

## 9. OPERATORS

Operators are matched using **maximal munch** (longest match first).

### A. Arithmetic Operators
| Token | Name             | Description                    | Origin     |
| ----- | ---------------- | ------------------------------ | ---------- |
| `+`   | Plus             | Addition / string concat       | Both       |
| `-`   | Minus            | Subtraction / unary negation   | Both       |
| `*`   | Star             | Multiplication / splat         | Both       |
| `/`   | Slash            | Division                       | Both       |
| `%`   | Modulo           | Remainder                      | Both       |
| `**`  | Power            | Exponentiation                 | Both       |
| `//`  | Floor Division   | Integer division               | Python     |

### B. Assignment Operators
| Token  | Name              | Description                   | Origin     |
| ------ | ----------------- | ----------------------------- | ---------- |
| `=`    | Assign            | Variable assignment           | Both       |
| `+=`   | Add-Assign        | Add and assign                | Both       |
| `-=`   | Sub-Assign        | Subtract and assign           | Both       |
| `*=`   | Mul-Assign        | Multiply and assign           | Both       |
| `/=`   | Div-Assign        | Divide and assign             | Both       |
| `%=`   | Mod-Assign        | Modulo and assign             | Both       |
| `**=`  | Pow-Assign        | Power and assign              | Both       |
| `//=`  | Floor-Div-Assign  | Floor divide and assign       | Python     |
| `:=`   | Walrus            | Assignment expression         | Python     |

### C. Comparison / Relational Operators
| Token  | Name              | Description                   | Origin     |
| ------ | ----------------- | ----------------------------- | ---------- |
| `==`   | Equal             | Equality check                | Both       |
| `!=`   | Not Equal         | Inequality check              | Both       |
| `<`    | Less Than         | Less than                     | Both       |
| `>`    | Greater Than      | Greater than                  | Both       |
| `<=`   | Less or Equal     | Less than or equal            | Both       |
| `>=`   | Greater or Equal  | Greater than or equal         | Both       |
| `<=>`  | Spaceship         | Three-way comparison (-1,0,1) | Ruby       |
| `===`  | Case Equality     | Case/pattern match equality   | Ruby       |

### D. Logical Operators
| Token  | Name         | Description          | Origin     |
| ------ | ------------ | -------------------- | ---------- |
| `&&`   | AND (symbol) | Logical AND          | Ruby       |
| `\|\|` | OR (symbol)  | Logical OR           | Ruby       |
| `!`    | NOT (symbol) | Logical NOT          | Ruby       |
| `and`  | AND (word)   | Logical AND (low precedence) | Both |
| `or`   | OR (word)    | Logical OR (low precedence)  | Both |
| `not`  | NOT (word)   | Logical NOT (low precedence) | Both |

### E. Bitwise Operators
| Token  | Name           | Description          | Origin     |
| ------ | -------------- | -------------------- | ---------- |
| `&`    | Bitwise AND    | AND                  | Both       |
| `\|`   | Bitwise OR     | OR                   | Both       |
| `^`    | Bitwise XOR    | XOR                  | Both       |
| `~`    | Bitwise NOT    | Complement           | Both       |
| `<<`   | Left Shift     | Shift left / append  | Both       |
| `>>`   | Right Shift    | Shift right          | Both       |

### F. Range Operators (from Ruby)
| Token  | Name            | Description                    | Origin |
| ------ | --------------- | ------------------------------ | ------ |
| `..`   | Inclusive Range  | From start to end (inclusive)  | Ruby   |
| `...`  | Exclusive Range  | From start to end (exclusive)  | Ruby   |

### G. Special Operators
| Token  | Name           | Description                      | Origin     |
| ------ | -------------- | -------------------------------- | ---------- |
| `@`    | Decorator      | Decorator prefix (before def)    | Python     |
| `?:`   | Ternary        | Conditional expression           | Ruby       |
| `&.`   | Safe Navigator | Nil-safe method call             | Ruby       |

### Operator Regex (ordered longest → shortest):
```
OPERATOR_PATTERN = r'(\*\*=|//=|<=>|===|\*\*|//|<=|>=|==|!=|:=|&&|\|\||<<|>>|\.\.\.|\.\.|&\.|[+\-*/%&|^~]=|[+\-*/%<>=!&|^~@])'
```

---

## 10. LITERALS

### A. Integer Literals
**Rules:**
1. Sequence of digits `0-9`.
2. Underscores allowed as visual separators (from Python & Ruby): `1_000_000`.
3. No leading zeros except `0` itself (or prefix notation).
4. Supports base prefixes:
   - `0b` or `0B` — binary (`0b1010`)
   - `0o` or `0O` — octal (`0o755`)
   - `0x` or `0X` — hexadecimal (`0xFF`)

**Regex:** `0[bBoOxX][0-9a-fA-F_]+|[0-9][0-9_]*`

**Examples:**
```
0
42
1_000_000
0xFF
0b1010
0o755
```

### B. Float Literals
**Rules:**
1. Must contain a decimal point with digits on both sides.
2. May contain scientific notation with `e` or `E`.
3. Underscores allowed as visual separators.

**Regex:** `[0-9][0-9_]*\.[0-9][0-9_]*([eE][+-]?[0-9_]+)?`

**Examples:**
```
3.14
0.5
1_000.50
2.5e10
1.0E-3
```

### C. String Literals

#### Single-Quoted Strings (from Both)
- No escape sequences except `\\` and `\'`.
- No interpolation.

**Regex:** `'([^'\\]|\\.)*'`

**Examples:**
```
'hello world'
'it\'s fine'
```

#### Double-Quoted Strings (from Both)
- Supports escape sequences.
- Supports string interpolation with `#{expression}` (from Ruby).

**Regex:** `"([^"\\]|\\.)*"`

**Escape Sequences:**
| Escape | Meaning        |
| ------ | -------------- |
| `\n`   | Newline        |
| `\t`   | Tab            |
| `\\`   | Backslash      |
| `\"`   | Double quote   |
| `\#`   | Literal `#`    |
| `\0`   | Null character |
| `\a`   | Bell           |
| `\b`   | Backspace      |

**Interpolation Example:**
```
"Hello, #{name}! You are #{age} years old."
```

#### Triple-Quoted Strings (from Python)
- Multiline strings using `"""..."""` or `'''...'''`.
- The `"""` variant supports interpolation with `#{}`.

**Regex:** `(\"\"\"[\s\S]*?\"\"\"|'''[\s\S]*?''')`

**Example:**
```
"""
This is a
multiline string with #{interpolation}.
"""
```

#### F-Strings (from Python)
- Prefix `f` before a quoted string.
- Expressions inside `{}` are evaluated.

**Regex:** `f"([^"\\]|\\.)*"|f'([^'\\]|\\.)*'`

**Example:**
```
f"The result is {2 + 3}"
f'Hello {name.upper()}'
```

### D. Symbol Literals (from Ruby)
- Prefixed with `:` followed by an identifier.
- Immutable, interned strings used as identifiers/keys.

**Regex:** `:[a-zA-Z_][a-zA-Z0-9_]*[?!=]?`

**Examples:**
```
:name
:status
:empty?
```

### E. Regex Literals (from Ruby)
- Enclosed in `/pattern/flags`.
- Only recognized in contexts where division is not expected.

**Regex:** `/([^/\\]|\\.)*\/[imxo]*/`

**Flags:**
| Flag | Meaning            |
| ---- | ------------------ |
| `i`  | Case-insensitive   |
| `m`  | Multiline          |
| `x`  | Extended (verbose) |

**Example:**
```
/^[a-z]+$/i
/\d{3}-\d{4}/
```

### F. Boolean Literals
```
true / True    →   Boolean true
false / False  →   Boolean false
```
> Matched as **keywords** by the lexer.

### G. Null Literals
```
nil / None     →   Null/no value
```
> Matched as **keywords** by the lexer.

---

## 11. COMMENTS

### Single-Line Comments (from Both Python and Ruby)
- Starts with `#`, extends to end of line.
- Discarded by the lexer.

**Regex:** `#[^\n]*`

**Example:**
```
# This is a comment
x = 10  # inline comment
```

### Multi-Line Comments (from Ruby)
- Block comments using `=begin` to `=end` (must be at the start of a line).
- Discarded by the lexer.

**Regex (multiline mode):** `^=begin\n[\s\S]*?\n=end$`

**Example:**
```
=begin
This is a block comment.
It can span multiple lines.
=end
```

---

## 12. TOKEN PRECEDENCE (Lexer Match Order)

The lexer must attempt to match tokens in this exact order to avoid ambiguity:

```
1. Whitespace           →  skip spaces/tabs; emit NEWLINE for \n
2. Comments             →  #... or =begin...=end (discard)
3. Triple-Quoted Strings →  """...""" or '''...''' (before regular strings)
4. F-Strings            →  f"..." or f'...'
5. String Literals      →  "..." or '...'
6. Regex Literals       →  /.../ (context-dependent)
7. Numeric Literals     →  integers and floats (with base prefixes)
8. Symbol Literals      →  :identifier
9. Operators            →  longest match first (**=, //=, <=>, etc.)
10. Delimiters          →  ( ) { } [ ] , . : ; -> =>
11. Identifiers/Keywords →  match identifier pattern, then check keyword set
12. Unknown Character   →  emit ERROR token
```

---

## 13. TOKEN OUTPUT FORMAT

Each token produced by the lexer should contain:

```
Token {
    type:    TOKEN_TYPE,     # e.g., KEYWORD, IDENTIFIER, INTEGER, STRING, etc.
    value:   "raw_value",    # the actual matched string from source
    line:    line_number,    # 1-indexed
    column:  column_number   # 1-indexed
}
```

### Example Token Types Enum:
```
KEYWORD, IDENTIFIER, INTEGER, FLOAT, STRING, FSTRING,
SYMBOL, REGEX, OPERATOR, DELIMITER, NEWLINE, COMMENT,
INDENT, DEDENT, EOF, ERROR
```

---

## 14. ERROR HANDLING

| Error                        | Example           | Lexer Action                      |
| ---------------------------- | ----------------- | --------------------------------- |
| Unterminated string          | `"hello...`       | Emit ERROR token with line/column |
| Unterminated regex           | `/pattern`        | Emit ERROR token                  |
| Invalid character            | Unicode symbols in wrong context | Emit ERROR token, continue |
| Invalid number format        | `09`, `1.`        | Emit ERROR token                  |
| Identifier starts with digit | `2name`           | Emit ERROR token                  |
| Unterminated block comment   | `=begin` without `=end` | Emit ERROR token            |
| Invalid escape sequence      | `"\q"`            | Emit WARNING, treat as literal    |

---

## 15. BASIC SYNTAX STYLE

### Function Definition
```
# Python-style with Ruby block influence
def greet(name)
    return "Hello, #{name}!"
end

# With type hints (Python-style)
def add(a, b) -> int
    return a + b
end
```

### Conditional
```
# Python-style
age = 19
if age >= 18
    puts "Allowed"
elif age >= 13
    puts "With guardian"
else
    puts "Not allowed"
end

# Ruby-style unless
unless age < 18
    puts "You may enter"
end
```

### Loops
```
# Python-style for-in
for i in 0..4
    puts i
end

# Ruby-style while
while count > 0 do
    count -= 1
end

# Ruby-style until
until done
    process()
end
```

### Classes
```
class Animal
    def initialize(@name, @sound)
    end

    def speak()
        return f"{@name} says #{@sound}!"
    end
end

class Dog < Animal
    def fetch(item)
        puts "#{@name} fetches the #{item}"
    end
end
```

### Exception Handling
```
# Python-style
try
    result = risky_operation()
except ValueError => e
    puts "Error: #{e}"
finally
    cleanup()
end

# Ruby-style
begin
    result = risky_operation()
rescue => e
    puts "Error: #{e}"
ensure
    cleanup()
end
```

---

## 16. SUMMARY: PYTHON vs RUBY ORIGINS

| Feature                       | From Python         | From Ruby              |
| ----------------------------- | ------------------- | ---------------------- |
| `#` single-line comments      | ✅                  | ✅                     |
| `=begin...=end` block comments|                     | ✅                     |
| Newline as statement terminator| ✅                 |                        |
| Implicit line continuation    | ✅ (inside brackets)|                        |
| Explicit line continuation `\`| ✅                  |                        |
| `_` in number literals        | ✅                  | ✅                     |
| Base prefixes `0b` `0o` `0x`  | ✅                  | ✅                     |
| Triple-quoted strings         | ✅                  |                        |
| F-strings `f"..."`            | ✅                  |                        |
| String interpolation `#{}`    |                     | ✅                     |
| Symbol literals `:name`       |                     | ✅                     |
| Regex literals `/pattern/`    |                     | ✅                     |
| `@` instance variables        |                     | ✅                     |
| `@@` class variables          |                     | ✅                     |
| `$` global variables          |                     | ✅                     |
| `?` `!` `=` method suffixes   |                     | ✅                     |
| `**` exponentiation           | ✅                  | ✅                     |
| `//` floor division           | ✅                  |                        |
| `:=` walrus operator          | ✅                  |                        |
| `<=>` spaceship operator      |                     | ✅                     |
| `===` case equality           |                     | ✅                     |
| `..` `...` range operators    |                     | ✅                     |
| `&.` safe navigation          |                     | ✅                     |
| `@` decorator syntax          | ✅                  |                        |
| `&&` `\|\|` `!` logical ops   |                     | ✅                     |
| `and` `or` `not` word ops     | ✅                  | ✅                     |
| `->` lambda / annotation      | ✅                  |                        |
| `=>` hash rocket              |                     | ✅                     |
| `unless` / `until`            |                     | ✅                     |
| `elif` keyword                | ✅                  |                        |
| `try` / `except` / `finally`  | ✅                  |                        |
| `begin` / `rescue` / `ensure` |                     | ✅                     |
| `import` / `from` / `as`      | ✅                  |                        |
| `require` / `include`          |                     | ✅                     |
| `with` context manager        | ✅                  |                        |
| `async` / `await`             | ✅                  |                        |
| `yield`                       | ✅                  | ✅                     |
| `lambda`                      | ✅                  | ✅                     |
| `self`                        | ✅                  | ✅                     |
| `pass` keyword                | ✅                  |                        |
| `do...end` blocks             |                     | ✅                     |
| `__dunder__` methods          | ✅                  |                        |
