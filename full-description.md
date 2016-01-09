---
layout: page
title: Full description
permalink: /description/
show: yes
---

This is the full description of how Firefly III works. A note: since Firefly III has been built on how _I_ manage my finances, please read this carefully so we match on how to do things.

## The general idea

After being a grownup for a while I noticed I was living from paycheck to paycheck and it kind of sucked. So I decided to clean house. First thing I did was simple: start living from the first day of the month until the last. This meant that I had to do a few things:

1. Get paid. _Immediately_ with no penny missing, I dropped this entire amount into my savings account.
2. That first month (I get paid on the 23rd) I had to stop spending money from that salary for a full week until the month was over. This was _hard_.
3. At the end of the month I moved the money back to my checking account. So I moved it away on the 23rd, and moved it back on the 1st.
4. Then, I lived through the month. Up until the 23rd everything was going fine. I kept track of _all_ my expenses.
5. On the 23rd (the second time) I got paid again. Yay! Quickly move to step 1 and put all of that money on my savings account.
6. Finish the month. Preferably not in the red.
7. It was the first of the month again. Move the money to my checking account, and back to step one.

This procedure allowed me to program a very simple tool (the original Firefly) which held very simple things: transactions. Oh and some accounts to keep track of the money. But that was basically it. It showed me where my money went.

In those early, exciting days, all I did was live from the 1st to the 31st of the month. This helped me save a lot of money already. The rest followed. Read below!

## Accounts

There are three kinds of accounts in Firefly III:

* **Asset accounts**
  * Asset accounts hold your money. Your bank account is an asset account. Your savings account is an asset account. They would be called "Savings account" or "Checking account". These accounts can be created with an initial (negative) balance, which is useful since you won't be entering your entire financial history.
* **Expense accounts**
  * Expense accounts are stores, shops, online things, whatever. For example: "Target", "Thinkgeek" or wherever you get stuff.
* **Revenue accounts**
  * Revenue accounts are the places you get money from. Ie. "my mom", "my job" or "the gubberment".

This split is there because my Googling into accounting has learned me that you should split these up. Internally too, even accounts with the same name but different types are split up. For example, if you shop at the place you work at, you will have a revenue account called "Albert Heijn" from which your salary is drawn but _also_ an expense account called "Albert Heijn" where you pay your groceries.

## Transactions

A transaction is a very simple thing. Money moves from A to B. It doesn't matter if this is an expense, your salary or you moving money around: money moves from A to B.

``Savings account -> € 200 -> Checking account``

In Firefly and most other systems this is stored using a "[double-entry bookkeeping system](http://en.wikipedia.org/wiki/Double-entry_bookkeeping_system)". You get money and your boss loses it. You spend money and the Albert Heijn "earns" it:

`` Your boss (- €1000) -> You (+ €1000)``

``You (- €15) -> Albert Heijn (+ €15)``

This seems pretty pointless but it is useful when transferring money back and forth between your own accounts. This is the same as spending money. It's all moving money around. This helps maintaining the internal consistency of the database.

Transactions have a few useful fields: a description, the amount (duh), the date, the accounts involved (from and to) and some meta-information.

In Firefly, a transaction can be a withdrawal, a deposit or a transfer. Beyond the obvious, they are slightly different from one another:

- Withdrawals have a dynamic "expense account" which you can fill in freely. If you go to a new store, simply enter the withdrawal with the new store as the expense account, and Firefly will start tracking it automatically.
- Deposits don't have budgets, but do have dynamic "revenue accounts". This works in the same way as withdrawals do.
- Transfers can be linked to piggy banks. So you could move € 200 to your savings account and have it added to your piggy bank "new couch". Transfers don't have budgets either.

## Budgets

Once you start creating transactions you start to realise that in a month, the same kind of stuff always comes back:

* Bills
* Groceries
* Cigarettes
* Going out for drinks
* Clothing

These are budgets. Budgets are a kind of "category" that come back every single month. Bills are returning (rent, water, electricity). You buy groceries every day. You need to pay rent every month. 

In what is called an "[envelope system](http://en.wikipedia.org/wiki/Envelope_system)" you stuff money in envelopes and spend your money from those envelopes.

Firefly III uses this method, which means you can create "envelopes" for any period. Example: € 200,- for "groceries" or € 500,- for "bills" every month.

## Categories

Categories are slightly different. You might start to notice how some things don't need a budget, but do need some kind of meta-thing. A category might work. "Furniture", "interest", "shoes" and "lunch" are perfect categories. If you create those in combination with budgets you can see exactly where your money is going. Other examples:

* Daily groceries
* Money management
* Lunch
* Car
* Public transport
* House

Firefly III allows you to dynamically create and manage categories. Fancy charts will show you how your money is divided over categories.

### The difference between categories and budgets

If you can save money every month on a certain subject, it's a budget. Groceries are budget. Bills are a budget. If you travel by train occasionally, it's not a budget.

First and foremost: a category is "incidental". You don't buy new furniture every month but you might want to keep track of such expenses. Or you don't care about costs for public traffic (budget-wise) but a category would be nice.

The rule of thumb is: would you make a real life envelope for it? If yes: budget. If no: category.

## Tags

Tags are an extension of categories and meant to expand on the meta-data included in a transaction. The idea is that you could add stuff like "should-not-have-bought-this" or "overly-expensive-gadget".

Tags are currently implemented in Firefly, although you cannot _see_ them everywhere. Nor can you see nice overviews of tags. This is all coming. However, some important features of tags have already been implemented.

### Advance payments

Let's say you're in a restaurant, and decide to pay the bill for everybody to save the hassle. Still, you agreed to [go Dutch](https://en.wikipedia.org/wiki/Going_Dutch) so everybody should pay there share. Instead of paying you on the spot, they're going to transfer the money to you.

Advance payment tags allow you to join the original expense and any deposits. So if you paid 150,- in advance and get 120,- back from your friends, Firefly III will only show you a 30,- expense. Neat huh?

### Balancing money

Sometimes I use money from my savings account for special expenses. For example, I bought fancy headset. I paid for it like I usually do. 100,- from my checking account. However, I simultaneously transfer 100,- from my savings account. This effectively "nulls" the transaction. It hasn't "cost" me anything, except the money from my savings account.

Using "balancing act" tags I can nullify such transactions so they won't mess up my budgets.

## Piggy banks

If you want to buy something expensive, you might need to save for it. Use piggy banks to save money. You can create piggy banks, set a target amount and start putting money in them. 

The general gist is that saving money is difficult. So you could set a target amount, even set a target date and get reminded of when to add money to a piggy bank. If you have one year to save € 1200,- you could remind yourself monthly and Firefly will tell you to save € 100,- every month.

## Bills

Rent. Comes back every month. Create a bill and Firefly will not only match new withdrawals to bills but also show you which bills are still due and which ones aren't.

## Reports 

Speaks for itself.