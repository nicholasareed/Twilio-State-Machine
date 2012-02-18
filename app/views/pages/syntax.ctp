
_________
U S A G E

Block modifier syntax:

    Header: h(1-6).
    Paragraphs beginning with 'hn. ' (where n is 1-6) are wrapped in header tags.
    Example: h1. Header... -> <h1>Header...</h1>

    Paragraph: p. (also applied by default)
    Example: p. Text -> <p>Text</p>

    Blockquote: bq.
    Example: bq. Block quotation... -> <blockquote>Block quotation...</blockquote>

    Blockquote with citation: bq.:http://citation.url
    Example: bq.:http://textism.com/ Text...
    ->  <blockquote cite="http://textism.com">Text...</blockquote>

    Footnote: fn(1-100).
    Example: fn1. Footnote... -> <p id="fn1">Footnote...</p>

    Numeric list: #, ##
    Consecutive paragraphs beginning with # are wrapped in ordered list tags.
    Example: <ol><li>ordered list</li></ol>

    Bulleted list: *, **
    Consecutive paragraphs beginning with * are wrapped in unordered list tags.
    Example: <ul><li>unordered list</li></ul>

Phrase modifier syntax:

           _emphasis_   ->   <em>emphasis</em>
           __italic__   ->   <i>italic</i>
             *strong*   ->   <strong>strong</strong>
             **bold**   ->   <b>bold</b>
         ??citation??   ->   <cite>citation</cite>
       -deleted text-   ->   <del>deleted</del>
      +inserted text+   ->   <ins>inserted</ins>
        ^superscript^   ->   <sup>superscript</sup>
          ~subscript~   ->   <sub>subscript</sub>
               @code@   ->   <code>computer code</code>
          %(bob)span%   ->   <span class="bob">span</span>

        ==notextile==   ->   leave text alone (do not format)

       "linktext":url   ->   <a href="url">linktext</a>
 "linktext(title)":url  ->   <a href="url" title="title">linktext</a>

           !imageurl!   ->   <img src="imageurl" />
  !imageurl(alt text)!  ->   <img src="imageurl" alt="alt text" />
    !imageurl!:linkurl  ->   <a href="linkurl"><img src="imageurl" /></a>

ABC(Always Be Closing)  ->   <acronym title="Always Be Closing">ABC</acronym>


Table syntax:

    Simple tables:

        |a|simple|table|row|
        |And|Another|table|row|

        |_. A|_. table|_. header|_.row|
        |A|simple|table|row|

    Tables with attributes:

        table{border:1px solid black}.
        {background:#ddd;color:red}. |{}| | | |


Applying Attributes:

    Most anywhere Textile code is used, attributes such as arbitrary css style,
    css classes, and ids can be applied. The syntax is fairly consistent.

    The following characters quickly alter the alignment of block elements:

        <  ->  left align    ex. p<. left-aligned para
        >  ->  right align       h3>. right-aligned header 3
        =  ->  centred           h4=. centred header 4
        <> ->  justified         p<>. justified paragraph

    These will change vertical alignment in table cells:

        ^  ->  top         ex. |^. top-aligned table cell|
        -  ->  middle          |-. middle aligned|
        ~  ->  bottom          |~. bottom aligned cell|

    Plain (parentheses) inserted between block syntax and the closing dot-space
    indicate classes and ids:

        p(hector). paragraph -> <p class="hector">paragraph</p>

        p(#fluid). paragraph -> <p id="fluid">paragraph</p>

        (classes and ids can be combined)
        p(hector#fluid). paragraph -> <p class="hector" id="fluid">paragraph</p>

    Curly {brackets} insert arbitrary css style

        p{line-height:18px}. paragraph -> <p style="line-height:18px">paragraph</p>

        h3{color:red}. header 3 -> <h3 style="color:red">header 3</h3>

    Square [brackets] insert language attributes

        p[no]. paragraph -> <p lang="no">paragraph</p>

        %[fr]phrase% -> <span lang="fr">phrase</span>

    Usually Textile block element syntax requires a dot and space before the block
    begins, but since lists don't, they can be styled just using braces

        #{color:blue} one  ->  <ol style="color:blue">
        # big                   <li>one</li>
        # list                  <li>big</li>
                                <li>list</li>
                               </ol>

    Using the span tag to style a phrase

        It goes like this, %{color:red}the fourth the fifth%
              -> It goes like this, <span style="color:red">the fourth the fifth</span>
